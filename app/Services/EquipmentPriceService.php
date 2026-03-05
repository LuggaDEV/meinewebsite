<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class EquipmentPriceService
{
    /**
     * Fetch product page and extract current price, original ("Statt") price and discount percentage.
     *
     * @return array{price: string|null, original_price: string|null, discount_percentage: string|null}
     */
    public function getPriceAndDiscountFromUrl(string $url): array
    {
        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                ])
                ->get($url);

            if (! $response->successful()) {
                return ['price' => null, 'original_price' => null, 'discount_percentage' => null];
            }

            $html = $response->body();
            $dom = new \DOMDocument;
            @$dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NOERROR | LIBXML_NOWARNING);
            $xpath = new \DOMXPath($dom);

            $priceContext = $this->getAmazonPriceContext($xpath);

            return [
                'price' => $this->extractPriceFromHtml($xpath, $priceContext),
                'original_price' => $this->extractOriginalPriceFromHtml($xpath, $priceContext),
                'discount_percentage' => $this->extractDiscountPercentageFromHtml($xpath, $priceContext),
            ];
        } catch (\Throwable $e) {
            return ['price' => null, 'original_price' => null, 'discount_percentage' => null];
        }
    }

    /**
     * Extract price, original price and discount percentage from already-fetched HTML.
     *
     * @return array{price: string|null, original_price: string|null, discount_percentage: string|null}
     */
    public function getPriceAndDiscountFromHtml(string $html): array
    {
        try {
            $dom = new \DOMDocument;
            @$dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NOERROR | LIBXML_NOWARNING);
            $xpath = new \DOMXPath($dom);
            $priceContext = $this->getAmazonPriceContext($xpath);

            return [
                'price' => $this->extractPriceFromHtml($xpath, $priceContext),
                'original_price' => $this->extractOriginalPriceFromHtml($xpath, $priceContext),
                'discount_percentage' => $this->extractDiscountPercentageFromHtml($xpath, $priceContext),
            ];
        } catch (\Throwable $e) {
            return ['price' => null, 'original_price' => null, 'discount_percentage' => null];
        }
    }

    /**
     * @deprecated Use getPriceAndDiscountFromUrl() instead.
     */
    public function getPriceFromUrl(string $url): ?string
    {
        $result = $this->getPriceAndDiscountFromUrl($url);

        return $result['price'];
    }

    /**
     * Parse "13,99 €" or "29,90 €" to float for comparison.
     */
    public function parsePriceToFloat(?string $price): ?float
    {
        if ($price === null || trim($price) === '') {
            return null;
        }
        $normalized = preg_replace('/[^\d,.]/', '', $price);
        $normalized = str_replace(',', '.', $normalized);
        if ($normalized === '' || ! is_numeric($normalized)) {
            return null;
        }

        return (float) $normalized;
    }

    /**
     * Amazon desktop price widget context so we extract from the main price block, not other offers.
     */
    private function getAmazonPriceContext(\DOMXPath $xpath): ?\DOMNode
    {
        $div = $xpath->query("//*[@id='corePriceDisplay_desktop_feature_div']")->item(0);

        return $div instanceof \DOMNode ? $div : null;
    }

    private function queryOne(\DOMXPath $xpath, ?\DOMNode $context, string $path): ?\DOMNode
    {
        if ($context !== null) {
            $relative = str_starts_with($path, '//') ? '.'.$path : $path;
            $node = $xpath->query($relative, $context)->item(0);
            if ($node instanceof \DOMNode) {
                return $node;
            }
        }

        $node = $xpath->query($path)->item(0);

        return $node instanceof \DOMNode ? $node : null;
    }

    private function extractPriceFromHtml(\DOMXPath $xpath, ?\DOMNode $context = null): ?string
    {
        $whole = $this->queryOne($xpath, $context, "//span[contains(concat(' ', normalize-space(@class), ' '), ' a-price-whole ')]");
        $fraction = $this->queryOne($xpath, $context, "//span[contains(concat(' ', normalize-space(@class), ' '), ' a-price-fraction ')]");
        if ($whole === null || $fraction === null) {
            return null;
        }
        $wholeText = trim($whole->textContent ?? '');
        $fractionText = trim($fraction->textContent ?? '');
        if ($wholeText === '' && $fractionText === '') {
            return null;
        }
        $wholeClean = rtrim($wholeText, ',');
        $price = $fractionText !== '' ? $wholeClean.','.$fractionText : $wholeClean;
        $price = trim($price);
        if ($price === '' || $price === ',') {
            return null;
        }

        return $price.' €';
    }

    /**
     * Extract original / "Statt" / "UVP" price from HTML (e.g. Amazon span.apex-basisprice-value "139,00€").
     */
    private function extractOriginalPriceFromHtml(\DOMXPath $xpath, ?\DOMNode $context = null): ?string
    {
        $node = $this->queryOne($xpath, $context, "//span[contains(concat(' ', normalize-space(@class), ' '), ' apex-basisprice-value ') or contains(concat(' ', normalize-space(@class), ' '), ' basisPrice ')]");
        if ($node === null) {
            return null;
        }
        $text = trim($node->textContent ?? '');
        if ($text === '') {
            return null;
        }
        if (preg_match('/(\d+(?:[.,]\d+)?)\s*€?/u', $text, $m)) {
            $price = str_replace('.', ',', $m[1]);

            return $price.' €';
        }

        return null;
    }

    /**
     * Extract discount percentage from HTML (e.g. Amazon span.savingsPercentage "-26 %").
     */
    private function extractDiscountPercentageFromHtml(\DOMXPath $xpath, ?\DOMNode $context = null): ?string
    {
        $node = $this->queryOne($xpath, $context, "//span[contains(concat(' ', normalize-space(@class), ' '), ' savings-percentage ') or contains(concat(' ', normalize-space(@class), ' '), ' savingsPercentage ') or contains(concat(' ', normalize-space(@class), ' '), ' apex-savings-percentage ')]");
        if ($node === null) {
            return null;
        }
        $text = trim($node->textContent ?? '');
        if ($text === '') {
            return null;
        }
        if (preg_match('/[-−]?\s*(\d+)\s*%?/u', $text, $m)) {
            $value = (int) $m[1];
            if ($value > 0 && $value <= 100) {
                return (string) $value;
            }
        }

        return null;
    }
}

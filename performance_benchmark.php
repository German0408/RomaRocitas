<?php

/**
 * Performance Benchmark for Magazine Products Component
 *
 * This script measures the performance of the magazine flip interface
 * with various product counts and configurations.
 */

class MagazinePerformanceBenchmark
{
    private $results = [];

    public function runBenchmarks()
    {
        echo "=== Magazine Products Performance Benchmark ===\n\n";

        // Test different product counts
        $productCounts = [10, 50, 100, 200, 500];

        foreach ($productCounts as $count) {
            echo "Testing with {$count} products...\n";
            $this->benchmarkProductLoad($count);
            $this->benchmarkPageFlipInitialization($count);
            $this->benchmarkMemoryUsage($count);
            echo "\n";
        }

        $this->generateReport();
    }

    private function benchmarkProductLoad($productCount)
    {
        $startTime = microtime(true);

        // Simulate product loading (in real scenario, this would be database queries)
        $products = $this->generateMockProducts($productCount);

        // Simulate Livewire component processing
        $processedProducts = array_map(function($product) {
            return [
                'id' => $product['id'],
                'sku' => $product['sku'],
                'name' => $product['name'],
                'description' => $product['description'],
                'image_path' => $product['image_path'],
                'price' => $product['price'],
                'subcategory' => $product['subcategory'],
                'category' => $product['category'],
                'stock' => $product['stock'],
            ];
        }, $products);

        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000; // Convert to milliseconds

        $this->results["load_{$productCount}"] = [
            'operation' => 'Product Load',
            'count' => $productCount,
            'duration_ms' => round($duration, 2),
            'status' => $duration < 100 ? 'PASS' : ($duration < 500 ? 'WARNING' : 'FAIL')
        ];

        echo "  Product load: {$this->results["load_{$productCount}"]['duration_ms']}ms ({$this->results["load_{$productCount}"]['status']})\n";
    }

    private function benchmarkPageFlipInitialization($productCount)
    {
        $startTime = microtime(true);

        // Simulate PageFlip initialization time
        $products = $this->generateMockProducts($productCount);

        // Simulate page creation
        $isMobile = rand(0, 1); // Random mobile/desktop
        $pages = $this->createMockPages($products, $isMobile);

        // Simulate PageFlip init (approximate time based on page count)
        $initTime = count($pages) * 0.5 + 10; // Base time + per-page time
        usleep($initTime * 1000); // Convert to microseconds

        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000;

        $this->results["init_{$productCount}"] = [
            'operation' => 'PageFlip Init',
            'count' => $productCount,
            'duration_ms' => round($duration, 2),
            'status' => $duration < 200 ? 'PASS' : ($duration < 1000 ? 'WARNING' : 'FAIL')
        ];

        echo "  PageFlip init: {$this->results["init_{$productCount}"]['duration_ms']}ms ({$this->results["init_{$productCount}"]['status']})\n";
    }

    private function benchmarkMemoryUsage($productCount)
    {
        $startMemory = memory_get_usage();

        $products = $this->generateMockProducts($productCount);
        $processedProducts = array_map(function($product) {
            return array_merge($product, ['processed' => true]);
        }, $products);

        $endMemory = memory_get_usage();
        $memoryUsed = ($endMemory - $startMemory) / 1024 / 1024; // Convert to MB

        $this->results["memory_{$productCount}"] = [
            'operation' => 'Memory Usage',
            'count' => $productCount,
            'memory_mb' => round($memoryUsed, 2),
            'status' => $memoryUsed < 10 ? 'PASS' : ($memoryUsed < 50 ? 'WARNING' : 'FAIL')
        ];

        echo "  Memory usage: {$this->results["memory_{$productCount}"]['memory_mb']}MB ({$this->results["memory_{$productCount}"]['status']})\n";
    }

    private function generateMockProducts($count)
    {
        $products = [];
        for ($i = 1; $i <= $count; $i++) {
            $products[] = [
                'id' => $i,
                'sku' => 'TEST' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'name' => 'Test Product ' . $i,
                'description' => 'This is a test product description for product ' . $i . '. It has some detailed information about the product features and specifications.',
                'image_path' => '/images/products/test' . $i . '.jpg',
                'price' => rand(10, 1000),
                'subcategory' => 'Test Subcategory ' . (($i % 5) + 1),
                'category' => 'Test Category ' . (($i % 3) + 1),
                'stock' => rand(0, 100)
            ];
        }
        return $products;
    }

    private function createMockPages($products, $isMobile)
    {
        $pages = [];

        if ($isMobile) {
            // Single product per page
            foreach ($products as $product) {
                $pages[] = $this->createMockProductPage($product, true);
            }
        } else {
            // Two products per page
            for ($i = 0; $i < count($products); $i += 2) {
                $product1 = $products[$i];
                $product2 = $products[$i + 1] ?? null;
                $pages[] = $this->createMockDualPage($product1, $product2);
            }
        }

        return $pages;
    }

    private function createMockProductPage($product, $isMobile)
    {
        return '<div class="magazine-page">Product: ' . $product['name'] . '</div>';
    }

    private function createMockDualPage($product1, $product2)
    {
        $content = 'Product 1: ' . $product1['name'];
        if ($product2) {
            $content .= ' | Product 2: ' . $product2['name'];
        }
        return '<div class="magazine-page">' . $content . '</div>';
    }

    private function generateReport()
    {
        echo "\n=== Performance Report ===\n";
        echo str_repeat("=", 60) . "\n";
        echo sprintf("%-15s %-10s %-12s %-10s %-8s\n", "Operation", "Count", "Duration", "Memory", "Status");
        echo str_repeat("-", 60) . "\n";

        foreach ($this->results as $key => $result) {
            $duration = isset($result['duration_ms']) ? $result['duration_ms'] . 'ms' : '-';
            $memory = isset($result['memory_mb']) ? $result['memory_mb'] . 'MB' : '-';

            echo sprintf("%-15s %-10s %-12s %-10s %-8s\n",
                $result['operation'],
                $result['count'],
                $duration,
                $memory,
                $result['status']
            );
        }

        echo str_repeat("=", 60) . "\n\n";

        // Summary
        $passCount = count(array_filter($this->results, fn($r) => $r['status'] === 'PASS'));
        $warningCount = count(array_filter($this->results, fn($r) => $r['status'] === 'WARNING'));
        $failCount = count(array_filter($this->results, fn($r) => $r['status'] === 'FAIL'));

        echo "Summary:\n";
        echo "  PASS: {$passCount}\n";
        echo "  WARNING: {$warningCount}\n";
        echo "  FAIL: {$failCount}\n\n";

        // Recommendations
        echo "Recommendations:\n";
        if ($failCount > 0) {
            echo "  - Address FAILED tests - performance may be unacceptable for users\n";
        }
        if ($warningCount > 0) {
            echo "  - Review WARNING tests - performance may degrade on slower devices\n";
        }
        if ($passCount === count($this->results)) {
            echo "  - All tests PASSED - excellent performance!\n";
        }

        echo "\nTarget Performance Goals:\n";
        echo "  - Product load: < 100ms\n";
        echo "  - PageFlip init: < 200ms\n";
        echo "  - Memory usage: < 10MB\n";
        echo "  - Overall load time: < 2 seconds\n";
    }
}

// Run the benchmark
$benchmark = new MagazinePerformanceBenchmark();
$benchmark->runBenchmarks();
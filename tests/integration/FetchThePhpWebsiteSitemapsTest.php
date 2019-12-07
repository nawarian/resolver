<?php

declare(strict_types=1);

namespace Nawarian\Requirements;

use Generator;
use PHPUnit\Framework\TestCase;

class FetchThePhpWebsiteSitemapsTest extends TestCase
{
    private Resolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new Resolver();
    }

    public function testResolverFetchesSitemapsFromThePhpWebsite(): void
    {
        $service = new class {
            public string $en;
            public string $br;

            public function fetchSitemaps(): Generator
            {
                $this->en = yield wrap($this)->getSitemap('en');
                $this->br = yield wrap($this)->getSitemap('br');
            }

            public function getSitemap(string $language): string
            {
                return file_get_contents("https://thephp.website/{$language}/sitemap.xml");
            }
        };

        $this->resolver->resolve($service->fetchSitemaps());

        $this->assertNotNull($service->en);
        $this->assertNotNull($service->br);
    }

    public function testResolverFetchesSitemapsFromThePhpWebsiteUsingArrayYield(): void
    {
        $service = new class {
            public string $en;
            public string $br;

            public function fetchSitemaps(): Generator
            {
                list($this->en, $this->br) = yield [
                    wrap($this)->getSitemap('en'),
                    wrap($this)->getSitemap('br'),
                ];
            }

            public function getSitemap(string $language): string
            {
                return file_get_contents("https://thephp.website/{$language}/sitemap.xml");
            }
        };

        $this->resolver->resolve($service->fetchSitemaps());

        $this->assertNotNull($service->en);
        $this->assertNotNull($service->br);
    }
}

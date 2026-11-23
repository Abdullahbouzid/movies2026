<?php

namespace Tests\Unit;

use App\Support\VideoEmbed;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class VideoEmbedTest extends TestCase
{
    #[DataProvider('providerLinks')]
    public function test_provider_links_become_embeds(string $link, string $expected): void
    {
        $this->assertSame($expected, VideoEmbed::embedUrl($link));
    }

    public static function providerLinks(): array
    {
        return [
            'youtube watch' => [
                'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ?rel=0',
            ],
            'youtube short link' => [
                'https://youtu.be/dQw4w9WgXcQ',
                'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ?rel=0',
            ],
            'youtube shorts' => [
                'https://youtube.com/shorts/abc123XYZ',
                'https://www.youtube-nocookie.com/embed/abc123XYZ?rel=0',
            ],
            'youtube already embedded' => [
                'https://www.youtube.com/embed/dQw4w9WgXcQ',
                'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ?rel=0',
            ],
            'youtube with start time' => [
                'https://www.youtube.com/watch?v=dQw4w9WgXcQ&t=1m30s',
                'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ?rel=0&start=90',
            ],
            'vimeo' => [
                'https://vimeo.com/123456789',
                'https://player.vimeo.com/video/123456789',
            ],
            'vimeo player' => [
                'https://player.vimeo.com/video/123456789',
                'https://player.vimeo.com/video/123456789',
            ],
            'dailymotion' => [
                'https://www.dailymotion.com/video/x8abc',
                'https://www.dailymotion.com/embed/video/x8abc',
            ],
        ];
    }

    public function test_plain_web_pages_are_not_embeddable(): void
    {
        $this->assertNull(VideoEmbed::embedUrl('https://example.com/page.html'));
        $this->assertNull(VideoEmbed::embedUrl('https://example.com/trailer.mp4'));
        $this->assertNull(VideoEmbed::embedUrl('not a url'));
    }

    #[DataProvider('fileLinks')]
    public function test_media_files_are_played_natively(string $link): void
    {
        $this->assertSame(
            ['type' => VideoEmbed::TYPE_FILE, 'url' => $link],
            VideoEmbed::resolve($link),
        );
    }

    public static function fileLinks(): array
    {
        return [
            'mp4' => ['https://cdn.example.com/clip.mp4'],
            'webm' => ['https://cdn.example.com/clip.webm'],
            'local storage' => ['http://localhost/storage/movies/videos/clip.mp4'],
            'mov with query' => ['https://cdn.example.com/clip.mov?token=abc'],
        ];
    }

    public function test_unknown_and_empty_references_resolve_to_nothing(): void
    {
        $this->assertNull(VideoEmbed::resolve(null));
        $this->assertNull(VideoEmbed::resolve('   '));
        $this->assertNull(VideoEmbed::resolve('https://example.com/page.html'));
    }

    public function test_provider_links_resolve_as_embed_type(): void
    {
        $resolved = VideoEmbed::resolve('https://youtu.be/dQw4w9WgXcQ');

        $this->assertSame(VideoEmbed::TYPE_EMBED, $resolved['type']);
        $this->assertStringStartsWith('https://www.youtube-nocookie.com/embed/', $resolved['url']);
    }
}

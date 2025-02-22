<?php

namespace CodebarAg\Zammad\Tests\Feature;

use CodebarAg\Zammad\Events\ZammadResponseLog;
use CodebarAg\Zammad\Tests\Feature\Traits\WithTestData;
use CodebarAg\Zammad\Zammad;
use Illuminate\Support\Facades\Event;

uses(WithTestData::class);

beforeEach(function () {
    $this->createTestTicket();
    $this->createTestComment(withAttachment: true);
    Event::fake();
});

afterEach(function () {
    $this->cleanupTestData();
});

it('can download an attachment', function () {
    $content = (new Zammad())->attachment()->download(
        ticketId: $this->testTicket->id,
        commentId: $this->testComment->id,
        attachmentId: $this->testAttachment->id,
    );

    $this->assertNotEmpty($content);
    $this->assertSame(
        'This is a test file with some content to test attachments.',
        $content
    );
    Event::assertDispatched(ZammadResponseLog::class, 1);
})->group('attachments');

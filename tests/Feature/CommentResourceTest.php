<?php

namespace CodebarAg\Zammad\Tests\Feature;

use CodebarAg\Zammad\DTO\Attachment;
use CodebarAg\Zammad\DTO\Comment;
use CodebarAg\Zammad\Events\ZammadResponseLog;
use CodebarAg\Zammad\Tests\Feature\Traits\WithTestData;
use CodebarAg\Zammad\Zammad;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

uses(WithTestData::class);

beforeEach(function () {
    $this->createTestTicket();
    $this->createTestComment();
    Event::fake();
});

afterEach(function () {
    $this->cleanupTestData();
});

it('does show by ticket', function () {
    $comments = (new Zammad())->comment()->showByTicket($this->testTicket->id);

    $this->assertInstanceOf(Collection::class, $comments);

    $comments->each(function (Comment $comment) {
        $this->assertInstanceOf(Comment::class, $comment);
        $this->assertSame($this->testTicket->id, $comment->ticket_id);
    });

    Event::assertDispatched(ZammadResponseLog::class, 1);
})->group('comments');

it('does show comment', function () {
    $comment = (new Zammad())->comment()->show($this->testComment->id);

    $this->assertInstanceOf(Comment::class, $comment);
    $this->assertSame($this->testComment->id, $comment->id);
    Event::assertDispatched(ZammadResponseLog::class, 1);
})->group('comments');

it('does create comment', function () {
    $data = [
        'ticket_id' => $this->testTicket->id,
        'subject' => '::subject::',
        'body' => 'huhuhuu<br>huhuhuu<br>huhuhuu<br><br>',
        'content_type' => 'text/html',
        'attachments' => [
            [
                'filename' => 'test.txt',
                'data' => 'RHUgYmlzdCBlaW4g8J+OgSBmw7xyIGRpZSDwn4yN',
                'mime-type' => 'text/plain',
            ],
        ],
    ];

    $comment = (new Zammad())->comment()->create($data);

    $this->assertInstanceOf(Comment::class, $comment);
    $this->assertSame('::subject::', $comment->subject);
    $this->assertSame('huhuhuu<br>huhuhuu<br>huhuhuu<br><br>', $comment->body);
    $this->assertSame('text/html', $comment->content_type);
    $this->assertSame($this->testTicket->id, $comment->ticket_id);
    $this->assertCount(1, $comment->attachments);
    tap($comment->attachments->first(), function (Attachment $attachment) {
        $this->assertSame(30, $attachment->size);
        $this->assertSame('test.txt', $attachment->name);
        $this->assertSame('text/plain', $attachment->type);
    });
    Event::assertDispatched(ZammadResponseLog::class, 1);
    (new Zammad())->comment()->delete($comment->id);
    Event::assertDispatched(ZammadResponseLog::class, 2);
})->group('comments');

it('does parse body from comment', function () {
    $comment = (new Zammad())->comment()->show($this->testComment->id);

    $this->assertStringContainsString(
        $this->testComment->body,
        $comment->body,
    );
    $this->assertStringContainsString(
        $this->testComment->body,
        $comment->body_filtered,
    );
})->group('comments');

it('has a from name helper', function () {
    $comment = (new Zammad())->comment()->show($this->testComment->id);

    $this->assertSame(
        Str::before(Str::between($comment->from, '"', '"'), '<'),
        $comment->fromName(),
    );
})->group('comments', 'helpers');

it('has a from email helper', function () {
    $comment = (new Zammad())->comment()->show($this->testComment->id);

    $this->assertSame(
        Str::between($comment->from, '<', '>'),
        $comment->fromEmail(),
    );
})->group('comments', 'helpers');

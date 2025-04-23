<?php

namespace CodebarAg\Zammad\Tests\Feature\Traits;

use CodebarAg\Zammad\DTO\Attachment;
use CodebarAg\Zammad\DTO\Comment;
use CodebarAg\Zammad\DTO\Ticket;
use CodebarAg\Zammad\DTO\User;
use CodebarAg\Zammad\Zammad;

trait WithTestData
{
    protected ?Ticket $testTicket = null;

    protected ?Comment $testComment = null;

    protected ?Attachment $testAttachment = null;

    protected ?User $testUser = null;

    protected function getOrCreateTestUser(): User
    {
        if ($this->testUser) {
            return $this->testUser;
        }

        $data = [
            'email' => 'test@example.com',
            'firstname' => 'Test',
            'lastname' => 'User',
            'roles' => ['Customer'],
        ];

        $this->testUser = (new Zammad)->user()->searchOrCreateByEmail($data['email'], $data);

        return $this->testUser;
    }

    protected function createTestTicket(): Ticket
    {
        if ($this->testTicket) {
            return $this->testTicket;
        }

        // Ensure test user exists
        $testUser = $this->getOrCreateTestUser();

        $data = [
            'title' => 'Test Ticket '.time(),
            'group' => 'Users',  // Using 'Users' as it's a common default group
            'customer_id' => $testUser->id,
            'article' => [
                'body' => 'Test ticket body',
                'type' => 'note',
                'internal' => false,
            ],
        ];

        $this->testTicket = (new Zammad)->ticket()->create($data);

        return $this->testTicket;
    }

    protected function createTestComment(bool $withAttachment = false): Comment
    {
        if ($this->testComment && ! $withAttachment) {
            return $this->testComment;
        }

        $ticket = $this->createTestTicket();

        $data = [
            'ticket_id' => $ticket->id,
            'subject' => 'Test Comment '.time(),
            'body' => 'Test comment body',
            'content_type' => 'text/plain',
        ];

        if ($withAttachment) {
            $data['attachments'] = [
                [
                    'filename' => 'test.txt',
                    'data' => base64_encode('This is a test file with some content to test attachments.'),
                    'mime-type' => 'text/plain',
                ],
            ];
        }

        $this->testComment = (new Zammad)->comment()->create($data);

        if ($withAttachment && $this->testComment->attachments->isNotEmpty()) {
            $this->testAttachment = $this->testComment->attachments->first();
        }

        return $this->testComment;
    }

    protected function cleanupTestData(): void
    {
        if ($this->testTicket) {
            (new Zammad)->ticket()->delete($this->testTicket->id);
            $this->testTicket = null;
            $this->testComment = null;
            $this->testAttachment = null;
        }

        // We don't delete the test user as it might be used by other tests
        // and is harmless to keep around
        $this->testUser = null;
    }
}

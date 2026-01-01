<?php

use Irabbi360\LaravelApiInspector\Extractors\WebhookExtractor;

it('extracts webhooks from config', function () {
    config(['api-inspector.webhooks' => [
        'user.created' => [
            'event' => 'user.created',
            'description' => 'Fired when user is created',
            'url' => 'https://example.com/webhooks/user',
            'method' => 'POST',
            'payload' => ['id' => 'integer', 'email' => 'string'],
        ],
    ]]);

    $extractor = new WebhookExtractor;
    $webhooks = $extractor->extract();

    expect($webhooks)->toHaveCount(1);
    expect($webhooks[0]['name'])->toBe('user.created');
    expect($webhooks[0]['event'])->toBe('user.created');
});

it('formats webhook data correctly', function () {
    config(['api-inspector.webhooks' => [
        'order.shipped' => [
            'event' => 'order.shipped',
            'description' => 'Order shipped',
            'url' => 'https://example.com/webhooks/order',
            'method' => 'POST',
            'payload' => ['order_id' => 'integer'],
            'active' => true,
        ],
    ]]);

    $extractor = new WebhookExtractor;
    $webhooks = $extractor->extract();
    $webhook = $webhooks[0];

    expect($webhook['method'])->toBe('POST');
    expect($webhook['status'])->toBeTrue();
    expect($webhook['retry_policy']['max_attempts'])->toBe(3);
});

it('includes webhook examples', function () {
    config(['api-inspector.webhooks' => [
        'payment.completed' => [
            'event' => 'payment.completed',
            'description' => 'Payment completed',
            'url' => 'https://example.com/webhooks/payment',
            'examples' => [
                ['id' => 1, 'amount' => 99.99],
                ['id' => 2, 'amount' => 149.99],
            ],
        ],
    ]]);

    $extractor = new WebhookExtractor;
    $webhooks = $extractor->extract();

    expect($webhooks[0]['examples'])->toHaveCount(2);
});

it('sets default retry policy for webhooks', function () {
    config(['api-inspector.webhooks' => [
        'invoice.created' => [
            'event' => 'invoice.created',
            'description' => 'Invoice created',
        ],
    ]]);

    $extractor = new WebhookExtractor;
    $webhooks = $extractor->extract();
    $retryPolicy = $webhooks[0]['retry_policy'];

    expect($retryPolicy['max_attempts'])->toBe(3);
    expect($retryPolicy['backoff'])->toBe('exponential');
    expect($retryPolicy['initial_delay'])->toBe(1000);
});

it('handles multiple webhooks', function () {
    config(['api-inspector.webhooks' => [
        'event.one' => ['event' => 'event.one', 'description' => 'First'],
        'event.two' => ['event' => 'event.two', 'description' => 'Second'],
        'event.three' => ['event' => 'event.three', 'description' => 'Third'],
    ]]);

    $extractor = new WebhookExtractor;
    $webhooks = $extractor->extract();

    expect($webhooks)->toHaveCount(3);
});

it('validates webhook url', function () {
    config(['api-inspector.webhooks' => [
        'test.event' => [
            'event' => 'test.event',
            'url' => 'https://example.com/webhooks/test',
        ],
    ]]);

    $extractor = new WebhookExtractor;
    $webhooks = $extractor->extract();

    expect($webhooks[0]['url'])->toContain('https://example.com');
});

it('includes default headers for webhooks', function () {
    config(['api-inspector.webhooks' => [
        'test.event' => ['event' => 'test.event'],
    ]]);

    $extractor = new WebhookExtractor;
    $webhooks = $extractor->extract();

    expect($webhooks[0]['headers'])->toHaveKey('Content-Type');
    expect($webhooks[0]['headers']['Content-Type'])->toBe('application/json');
});

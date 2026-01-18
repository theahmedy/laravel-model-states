---
title: Transition history (metadata & timestamps)
weight: 7
---

## Overview

By default, Model States only stores the current state value on the model.

If you want per-transition timestamps and optional metadata, you can opt-in to transition history storage. When enabled, every successful transition is persisted with:

- `model_type`, `model_id`
- state `field`
- `from_state`, `to_state`
- `created_at`
- `meta_data` (nullable JSON)

## Setup

1) Publish the migration:

```bash
php artisan vendor:publish --tag=laravel-model-states-migrations
```

2) Run migrations:

```bash
php artisan migrate
```

3) Enable transition history storage:

```php
// config/model-states.php
'"'"'transition_history'"'"' => [
    '"'"'enabled'"'"' => true,
],
```

## Writing metadata

You can attach metadata to a transition using the `metaData:` named argument:

```php
$model->status->transitionTo(Published::class, metaData: [
    'approved_by' => 5,
    'comment' => 'Reviewed',
]);
```

## Reading metadata and timestamps

Once stored, metadata is immutable and can be read from the state instance:

```php
$model->status->metaData();  // array|null
$model->status->createdAt(); // DateTimeInterface|null
```

## Backward compatibility

- Transition history is disabled by default.
- When disabled, no extra queries happen and `metaData()` / `createdAt()` return `null`.
- Existing `transitionTo($state, ...$args)` calls keep working unchanged.

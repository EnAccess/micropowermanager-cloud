<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Subscription
 *
 * @package App\Models
 *
 * @property int $id
 * @property int $upgrade_id
 * @property string $expires
 * @property string $transaction_id
 */
class Subscription extends BaseModel
{
    public function upgrade(): BelongsTo
    {
        return $this->belongsTo(Upgrade::class);
    }
}

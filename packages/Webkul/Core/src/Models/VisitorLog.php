<?php

namespace Webkul\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Core\Contracts\VisitorLog as VisitorLogContract;
use Webkul\Customer\Models\CustomerProxy;

class VisitorLog extends Model implements VisitorLogContract
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'visitor_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ip_address',
        'url',
        'user_agent',
        'device_type',
        'referer',
        'customer_id',
        'session_id',
    ];

    /**
     * Get the customer associated with the visit.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(CustomerProxy::modelClass());
    }
}

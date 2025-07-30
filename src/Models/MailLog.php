<?php

namespace Eclipse\Core\Models;

use Eclipse\Core\Database\Factories\MailLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MailLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'site_id',
        'message_id',
        'from',
        'to',
        'cc',
        'bcc',
        'subject',
        'body',
        'headers',
        'attachments',
        'sender_id',
        'recipient_id',
        'status',
        'sent_at',
        'data',
        'opened',
        'delivered',
        'complaint',
        'bounced',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'headers' => 'array',
        'attachments' => 'array',
        'data' => 'array',
        'sent_at' => 'datetime',
        'opened' => 'datetime',
        'delivered' => 'datetime',
        'complaint' => 'datetime',
        'bounced' => 'datetime',
    ];

    /**
     * Get the site that the mail log belongs to.
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get the sender that the mail log belongs to.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the recipient that the mail log belongs to.
     */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    /**
     * Get the factory for the model.
     */
    protected static function newFactory()
    {
        return MailLogFactory::new();
    }
}

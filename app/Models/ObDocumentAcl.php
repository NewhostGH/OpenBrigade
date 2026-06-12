<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One access-control entry (ACE) on a document-library object. Grants (allow) or
 * refuses (deny) a set of `rights` to a principal on one folder or document.
 * Folder ACEs cascade to descendants — see {@see App\Services\DocumentAclService}.
 *
 * @property int $id
 * @property string $resource_type folder|document
 * @property int $resource_id
 * @property string $principal_type user|group|role|everyone
 * @property int $principal_id
 * @property string $effect allow|deny
 * @property int $rights bitmask of the RIGHT_* constants
 */
class ObDocumentAcl extends Model
{
    public const RIGHT_READ = 1;        // see the item exists / its metadata

    public const RIGHT_DOWNLOAD = 2;    // download the file

    public const RIGHT_WRITE = 4;       // modify: rename, retype, replace, move

    public const RIGHT_DELETE = 8;      // delete the item

    public const RIGHT_SHARE = 16;      // manage this item's ACL

    public const RIGHT_FULL = 32;       // full control (implies every right)

    public const EFFECT_ALLOW = 'allow';

    public const EFFECT_DENY = 'deny';

    public const TYPE_FOLDER = 'folder';

    public const TYPE_DOCUMENT = 'document';

    protected $table = 'ob_document_acl';

    protected $fillable = [
        'resource_type', 'resource_id', 'principal_type', 'principal_id',
        'effect', 'rights', 'created_by',
    ];

    protected $casts = [
        'resource_id' => 'integer',
        'principal_id' => 'integer',
        'rights' => 'integer',
        'created_by' => 'integer',
    ];

    /** All individual rights ORed together (what RIGHT_FULL expands to). */
    public const ALL_RIGHTS = self::RIGHT_READ | self::RIGHT_DOWNLOAD | self::RIGHT_WRITE
        | self::RIGHT_DELETE | self::RIGHT_SHARE | self::RIGHT_FULL;

    /**
     * Right bit => human label, in display order. Single source for the UI.
     *
     * @return array<int,string>
     */
    public static function rightLabels(): array
    {
        return [
            self::RIGHT_READ => 'Lecture',
            self::RIGHT_DOWNLOAD => 'Téléchargement',
            self::RIGHT_WRITE => 'Modification',
            self::RIGHT_DELETE => 'Suppression',
            self::RIGHT_SHARE => 'Partage',
            self::RIGHT_FULL => 'Contrôle total',
        ];
    }

    /** Expand a mask so RIGHT_FULL implies every right. */
    public static function expand(int $mask): int
    {
        return ($mask & self::RIGHT_FULL) ? self::ALL_RIGHTS : $mask;
    }
}

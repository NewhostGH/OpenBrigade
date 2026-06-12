<?php

use App\Services\DocumentAclService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-object ACL for the document library — granular rights on a single file or
 * folder, granted to a user / group / role / everyone, with explicit allow or
 * deny. Folder ACEs are inherited by descendant folders and documents; the
 * item's own ACEs override. See {@see DocumentAclService}.
 *
 *  - resource_type / resource_id : the folder (DF_ID) or document (D_ID).
 *  - principal_type / principal_id : who the rule targets (everyone → id 0).
 *  - effect : allow | deny (deny wins per-right).
 *  - rights : bitmask of {read, download, write, delete, share, fullcontrol}.
 *
 * No row for an item ⇒ the legacy section/type security still decides
 * (backwards compatible).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ob_document_acl', function (Blueprint $table) {
            $table->id();
            $table->enum('resource_type', ['folder', 'document']);
            $table->integer('resource_id');
            $table->enum('principal_type', ['user', 'group', 'role', 'everyone']);
            $table->integer('principal_id')->default(0);
            $table->enum('effect', ['allow', 'deny'])->default('allow');
            $table->unsignedInteger('rights')->default(0);
            $table->integer('created_by')->nullable();
            $table->timestamps();

            $table->unique(['resource_type', 'resource_id', 'principal_type', 'principal_id', 'effect'], 'ob_document_acl_ace_unique');
            $table->index(['resource_type', 'resource_id']);
            $table->index(['principal_type', 'principal_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ob_document_acl');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ldap_domains', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->boolean('enabled')->default(true);
            $table->integer('priority')->default(10);
            $table->string('host');
            $table->integer('port')->default(389);
            $table->string('base_dn');
            $table->string('username')->nullable();
            $table->text('password')->nullable();
            $table->integer('timeout')->default(5);
            $table->boolean('use_tls')->default(false);
            $table->boolean('use_starttls')->default(false);
            $table->string('auth_method')->default('bind'); // bind|upn
            $table->string('upn_suffix')->nullable();
            $table->string('user_filter')->default('(&(objectClass=person)(|(uid={login})(mail={login})))');
            $table->boolean('restrict_to_ou')->default(false);
            $table->timestamps();
        });

        Schema::create('ldap_attribute_maps', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ldap_domain_id')->constrained('ldap_domains')->cascadeOnDelete();
            $table->string('ldap_attr');
            $table->string('local_field');
            $table->boolean('overwrite')->default(false);
            $table->timestamps();
        });

        Schema::create('ldap_ou_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ldap_domain_id')->constrained('ldap_domains')->cascadeOnDelete();
            $table->string('ou_dn');
            $table->string('extra_filter')->nullable();
            $table->string('action'); // allow|deny|assign
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedInteger('section_id')->nullable();
            $table->integer('priority')->default(10);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ldap_ou_rules');
        Schema::dropIfExists('ldap_attribute_maps');
        Schema::dropIfExists('ldap_domains');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->text('description')->nullable()->after('title');
            $table->string('sku')->nullable()->unique()->after('description');
            $table->string('slug')->nullable()->unique()->after('sku');
            $table->foreignId('category_id')->nullable()->after('slug')->constrained()->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->after('category_id')->constrained()->nullOnDelete();
            $table->unsignedInteger('stock_quantity')->default(0)->after('brand_id');
            $table->string('status')->default('active')->after('stock_quantity');
            $table->decimal('discount', 8, 2)->default(0)->after('status');
            $table->string('image')->nullable()->after('discount');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['brand_id']);
            $table->dropColumn([
                'description', 'sku', 'slug', 'category_id', 'brand_id',
                'stock_quantity', 'status', 'discount', 'image', 'deleted_at',
            ]);
        });
    }
};
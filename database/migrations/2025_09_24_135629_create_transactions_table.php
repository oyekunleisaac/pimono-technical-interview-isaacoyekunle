<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
public function up()
{
Schema::create('transactions', function (Blueprint $table) {
$table->id();
$table->uuid('uuid')->unique();
$table->unsignedBigInteger('sender_id');
$table->unsignedBigInteger('receiver_id');
$table->decimal('amount', 20, 2);
$table->decimal('commission_fee', 20, 2);
$table->string('status')->default('completed');
$table->text('meta')->nullable();
$table->timestamps();


$table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
$table->foreign('receiver_id')->references('id')->on('users')->onDelete('cascade');


$table->index(['sender_id', 'created_at']);
$table->index(['receiver_id', 'created_at']);
});
}


public function down()
{
Schema::dropIfExists('transactions');
}
};
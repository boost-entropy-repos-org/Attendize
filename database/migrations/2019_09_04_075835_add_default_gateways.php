<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\PaymentGateway;

class AddDefaultGateways extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        PaymentGateway::where('name', 'PayPal_Express')->delete();

        Schema::table('payment_gateways', function($table) {
            $table->boolean('default')->default(0);
            $table->string('admin_blade_template', 150)->default('');
            $table->string('checkout_blade_template', 150)->default('');
        });

        DB::table('payment_gateways')
            ->where('provider_name', 'Stripe')
            ->update(['admin_blade_template' => 'ManageAccount.Partials.Stripe',
                      'checkout_blade_template' => 'Public.ViewEvent.Partials.PaymentStripe']);

        $dummyGateway = DB::table('payment_gateways')->where('name', '=', 'Dummy')->first();

        if ($dummyGateway === null) {
            // user doesn't exist
            DB::table('payment_gateways')->insert(
                array(
                    'provider_name' => 'Dummy/Test Gateway',
                    'provider_url' => 'none',
                    'is_on_site' => 1,
                    'can_refund' => 1,
                    'name' => 'Dummy',
                    'default' => 0,
                    'admin_blade_template' => '',
                    'checkout_blade_template' => 'Public.ViewEvent.Partials.Dummy'
                )
            );
        }

        $stripePaymentIntents = DB::table('payment_gateways')->where('name', '=', 'Stripe\PaymentIntents')->first();
        if ($stripePaymentIntents === null) {
            DB::table('payment_gateways')->insert(
                [
                    'provider_name' => 'Stripe SCA',
                    'provider_url' => 'https://www.stripe.com',
                    'is_on_site' => 0,
                    'can_refund' => 1,
                    'name' => 'Stripe\PaymentIntents',
                    'default' => 0,
                    'admin_blade_template' => 'ManageAccount.Partials.StripeSCA',
                    'checkout_blade_template' => 'Public.ViewEvent.Partials.PaymentStripeSCA'
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
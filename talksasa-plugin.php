<?php
/*
Plugin Name:Talksasa SMS Plugin
Description:This is Talksasa's custom plugin for sending an sms when an order status is changed or an order note is sent.
Version: 1.0
Author: Andrew Muchiri
*/

require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createMutable(__DIR__);
$dotenv->load();

 // Event of a new email for the order - change in status
 add_action( 'woocommerce_order_status_changed', 'techiepress_send_sms_on_new_order_status', 10, 4 );

 // Event of the order note
 add_action( 'woocommerce_new_customer_note_notification', 'techiepress_send_sms_on_new_order_notes', 10, 1 );

 function techiepress_send_sms_on_new_order_status( $order_id, $old_status, $new_status, $order) {
     // Get the order Object
     $my_order = wc_get_order( $order_id );

     $firstname = $my_order->get_billing_first_name(); // firstname
     $phone     = $my_order->get_billing_phone(); // Phone
    //  $shopname  = get_option('woocommerce_email_from_name');
     $default_sms_message = "Thank you $firstname for shopping with us. Your Order #$order_id is $new_status";

     techiepress_send_sms_to_customer( $phone, $default_sms_message );
 }

 function techiepress_send_sms_on_new_order_notes( $email_args ) {
     $order = wc_get_order( $email_args['order_id'] );
     $note  = $email_args['customer_note'];
     $default_sms_message = "Your order has been updated. $note";
    //  $shopname  = get_option('woocommerce_email_from_name');
     $phone = $order->get_billing_phone(); // Phone
     techiepress_send_sms_to_customer( $phone, $default_sms_message);
 }

 function techiepress_send_sms_to_customer( $phone, $default_sms_message) {
    $apiKey = $_ENV['API_KEY'];
    $sender_id = $_ENV['SENDER_ID'];

    if ( ! $phone ) {
        return 'no mobile number found';
    }

	$baseurl = "https://bulksms.talksasa.com/api/v3/sms/send";

    $ch = curl_init($baseurl);
    $data = array(
        "recipient" => $phone,
        "sender_id" => "$sender_id",
        "type" => "plain",
        "message" => $default_sms_message
    );
    $payload = json_encode($data);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json', "Authorization:Bearer $apiKey", 'Accept:application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = json_encode(curl_exec($ch));
    curl_close($ch);
 }




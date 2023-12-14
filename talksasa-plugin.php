<?php
/*
Plugin Name:Talksasa SMS Plugin
Description:This is a custom Wordpress plugin for sending an sms when an order status is changed or an order note is sent using Talksasa sms API
Version: 1.0
Author: Andrew Muchiri
*/

require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createMutable(__DIR__);
$dotenv->load();

add_action('woocommerce_order_status_changed', 'handleNewOrderStatus', 10, 4);
add_action('woocommerce_new_customer_note_notification', 'handleNewOrderNotes', 10, 1);

function handleNewOrderStatus($order_id, $old_status, $new_status, $order)
{
    $my_order = wc_get_order($order_id);//get the order object
    $firstname = $my_order->get_billing_first_name(); // firstname
    $phone = $my_order->get_billing_phone(); // Phone
    $default_sms_message = "Thank you $firstname for shopping with us. Your Order #$order_id is $new_status";
    sendSms($phone, $default_sms_message);
}

function handleNewOrderNotes($email_args)
{
    $order = wc_get_order($email_args['order_id']);
    $note  = $email_args['customer_note'];
    $default_sms_message = "Your order has been updated. $note";
    $phone = $order->get_billing_phone(); // Phone
    sendSms($phone, $default_sms_message);
}

function sendSms($phone, $default_sms_message)
{
    $apiKey = $_ENV['API_KEY'];
    $sender_id = $_ENV['SENDER_ID'];

    if (!$phone) {
        return 'no mobile number found';
    } else {
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
}

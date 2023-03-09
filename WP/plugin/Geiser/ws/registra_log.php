<?php
/**
 * Este arquivo é chamado pela rota /registra_log do plugin Meu Plugin
 */

// Inclui o arquivo wp-load.php para ter acesso às funções do WordPress
require_once( dirname( __FILE__ ) . '/wp-load.php' );

// Recupera os dados da tabela geiser_logs
global $wpdb;
$table_name = $wpdb->prefix . 'geiser_logs';
$logs = $wpdb->get_results( "SELECT * FROM $table_name" );

// Monta os dados para inserir na tabela geiser_logs
$data = array(
    'id_leitor' => 1,
    'nome' => 'Leitor 1',
    'ip' => $_SERVER['REMOTE_ADDR'],
    'lastdt' => current_time( 'mysql' ),
    'status' => 1
);
$wpdb->insert( $table_name, $data );

// Monta os dados para enviar na chamada GET
$params = array(
    'logs' => $logs
);

// Faz a chamada GET para incluir as informações em outro serviço
$url = 'http://outroservico.com/incluir_logs.php?' . http_build_query( $params );
$response = wp_remote_get( $url, array( 'timeout' => 120 ) );

// Verifica a resposta
if ( is_wp_error( $response ) ) {
    $error_message = $response->get_error_message();
    echo "Erro ao enviar informações: $error_message";
} else {
    echo "Informações enviadas com sucesso";
}


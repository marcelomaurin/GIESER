<?php
/**
 * Plugin Name: Meu Plugin
 * Plugin URI: https://meusite.com/meu-plugin
 * Description: Descrição do meu plugin.
 * Version: 1.0
 * Author: Meu Nome
 * Author URI: https://meusite.com
 */

// Cria as tabelas quando o plugin for ativado
register_activation_hook( __FILE__, 'criar_tabelas' );
function criar_tabelas() {
    global $wpdb;

    // Cria a tabela geiser_leitores
    $table_name1 = $wpdb->prefix . 'geiser_leitores';
    $charset_collate = $wpdb->get_charset_collate();
    $sql1 = "CREATE TABLE $table_name1 (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        nome varchar(50) NOT NULL,
        token varchar(50) NOT NULL,
        status TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
        PRIMARY KEY (id)
    ) $charset_collate;";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql1 );

    // Cria a tabela geiser_logs
    $table_name2 = $wpdb->prefix . 'geiser_logs';
    $sql2 = "CREATE TABLE $table_name2 (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        id_leitor mediumint(9) NOT NULL,
        nome varchar(50) NOT NULL,
        ip varchar(50) NOT NULL,
        lastdt datetime NOT NULL,
        status TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
        PRIMARY KEY (id),
        FOREIGN KEY (id_leitor) REFERENCES $table_name1(id)
    ) $charset_collate;";
    dbDelta( $sql2 );
}

function geiser_registra_log_endpoint( WP_REST_Request $request ) {
    $file_path = trailingslashit(ABSPATH) . 'ws/registra_log.php';
    $response = wp_remote_post( get_site_url() . '/' . $file_path, array( 'timeout' => 120 ) );
    return $response['body'];
}

// Adiciona a rota do web service para chamar o arquivo ./ws/registra_log.php
add_action( 'rest_api_init', 'geiser_register_api_routes' );
function geiser_register_api_routes() {
    //...
    register_rest_route( 'geiser/v1', '/registra_log', array(
        'methods' => 'GET',
        'callback' => 'geiser_registra_log_endpoint',
        'permission_callback' => function () {
            return current_user_can( 'manage_options' );
        }
    ) );
}

// Endpoint do web service para chamar o arquivo ./ws/registra_log.php
function geiser_registra_log_endpoint( WP_REST_Request $request ) {
    // Inclui o arquivo ./ws/registra_log.php
    require_once( dirname( __FILE__ ) . '/ws/registra_log.php' );
}




// Adiciona o menu no painel de administração
add_action( 'admin_menu', 'geiser_admin_menu' );
function geiser_admin_menu() {
    add_menu_page( 'Geiser Admin', 'Geiser Admin', 'manage_options', 'geiser-admin', 'geiser_admin_page', 'dashicons-chart-pie' );
    add_submenu_page( 'geiser-admin', 'Dispositivos', 'Dispositivos', 'manage_options', 'geiser-dispositivos', 'geiser_dispositivos_page' );
    add_submenu_page( 'geiser-admin', 'Logs', 'Logs', 'manage_options', 'geiser-logs', 'geiser_logs_page' );
    add_submenu_page( 'geiser-admin', 'Análise', 'Análise', 'manage_options', 'geiser-analise', 'geiser_analise_page' );
}

add_action( 'rest_api_init', 'geiser_register_api_routes' );
function geiser_register_api_routes() {
    register_rest_route( 'geiser/v1', '/logs', array(
        'methods' => 'POST',
        'callback' => 'geiser_logs_endpoint',
        'permission_callback' => function () {
            return current_user_can( 'manage_options' );
        }
    ) );
}


// Endpoint do web service para inserir dados na tabela geiser_logs
function geiser_logs_endpoint( WP_REST_Request $request ) {
    global $wpdb;

    $json = $request->get_json_params();

    // Insere os dados na tabela geiser_logs
    $table_name = $wpdb->prefix . 'geiser_logs';
    $data = array(
        'id_leitor' => $json['id_leitor'],
        'nome' => $json['nome'],
        'ip' => $json['ip'],
        'lastdt' => $json['lastdt'],
        'status' => $json['status']
    );
    $wpdb->insert( $table_name, $data );

    return 'Dados inseridos na tabela com sucesso.';
}


<?php
/**
 * Plugin Name: Geiser
 * Plugin URI: http://maurinsoft.com.br/geiser
 * Description: Gestão de Contador Geiser
 * Version: 1.0
 * Author: Marcelo Maurin Martins
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
        token varchar(50) NOT NULL,
        lastdt datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
	usvh float NOT NULL,
	temp float NOT NULL,
	hum  float NOT NULL,
        PRIMARY KEY (id)
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
add_action( 'rest_api_init', 'registrar_rota2_personalizada');


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
/*
function geiser_registra_log_endpoint( WP_REST_Request $request ) {
    // Inclui o arquivo ./ws/registra_log.php
    require_once( dirname( __FILE__ ) . '/ws/registra_log.php' );
}
*/



// Adiciona o menu no painel de administração
add_action( 'admin_menu', 'geiser_admin_menu' );
function geiser_admin_menu() {
    add_menu_page( 'Geiser Admin', 'Geiser Admin', 'manage_options', 'geiser-admin', 'geiser_admin_page', 'geiser_admin_page' );
    add_submenu_page( 'geiser-admin', 'Dispositivos', 'Dispositivos', 'manage_options', 'geiser_dispositivos_page', 'geiser_dispositivos_page' );
    add_submenu_page( 'geiser-admin', 'Logs', 'Logs', 'manage_options', 'geiser_logs_page', 'geiser_logs_page' );
    add_submenu_page( 'geiser-admin', 'Análise', 'Análise', 'manage_options', 'geiser_analise_page', 'geiser_analise_page' );
	 // Adiciona Chart.js ao painel de administração
    add_action('admin_enqueue_scripts', function () {
        wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js');
    });
}

add_action('rest_api_init', 'registrar_rota_personalizada');

function registrar_rota_personalizada() {
  register_rest_route('Geiser/v1', '/registro.php', [
    'methods' => 'GET',
    'callback' => 'geiser_lst_callback',
  ]);
}

function geiser_lst_callback(WP_REST_Request $request) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'geiser_logs';

    // Obtenha os parâmetros do corpo da solicitação
    $data_inicio = $request->get_param('data_inicio');
    $data_fim = $request->get_param('data_fim');
    $ip = $request->get_param('ip');

    // Construa a consulta SQL
    $sql = "SELECT * FROM {$table_name}";

    $where_clauses = array();
    if (!empty($data_inicio)) {
        $where_clauses[] = $wpdb->prepare('lastdt >= %s', $data_inicio);
    }
    if (!empty($data_fim)) {
        $where_clauses[] = $wpdb->prepare('lastdt <= %s', $data_fim);
    }
    if (!empty($ip)) {
        $where_clauses[] = $wpdb->prepare('ip = %s', $ip);
    }

    if (!empty($where_clauses)) {
        $sql .= ' WHERE ' . implode(' AND ', $where_clauses);
    }

    $sql .= ' ORDER BY lastdt DESC';

    // Execute a consulta e obtenha os resultados
    $logs = $wpdb->get_results($sql);

    // Retorne os resultados como uma resposta JSON
    return new WP_REST_Response($logs, 200);
}



function registrar_rota2_personalizada() {
  register_rest_route('Geiser/v1', '/registro.php', [
    'methods' => 'POST',
    'callback' => 'geiser_reg_callback',
  ]);
}

function geiser_reg_callback(WP_REST_Request $request) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'geiser_logs';
	
    $usvh = $request->get_param('usvh');
    $temp = $request->get_param('temp');
    $hum = $request->get_param('hum');
	
    
    // Obtenha o IP do cliente
    $ip = $_SERVER['REMOTE_ADDR'];
    //echo $ip;
    //echo "<br/>";

    $sql = "SELECT * FROM {$wpdb->prefix}geiser_leitores WHERE token = '$ip'";
    //echo $sql;
    //echo "<br/>";

    // Verificar se o dispositivo está cadastrado na tabela geiser_leitores
    $device = $wpdb->get_row($sql);

    //echo $device;
    //echo "<br/>";

    if ($usvh === null)
    {
        return new WP_REST_Response(array('error' => 'usvh nao definido'), 404);
    }
    if ($device === null) {
        return new WP_REST_Response(array('error' => 'Device nao cadastrado'), 404);
    }

    // Obtenha a data e hora atual do sistema
    $lastdt = date('Y-m-d H:i:s');

    // O campo status deve ser sempre 1
    $status = 1;

    // Insira os dados na tabela
    $wpdb->insert(
        $table_name,
        array(
            'usvh' => $usvh,
            'temp' => $temp,
            'hum' => $hum,
            'token' => $ip
        ),
        array('%s', '%s', '%s', '%s', '%d', '%d', '%s')
    );

    // Retorne uma resposta JSON com uma mensagem de sucesso
    return new WP_REST_Response(array('message' => 'Registro inserido com sucesso!'), 200);
}


function geiser_admin_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'geiser_leitores';

    // Processa a ação de adicionar um novo registro
    if (isset($_POST['action']) && $_POST['action'] == 'add') {
        $nome = sanitize_text_field($_POST['nome']);
        $token = sanitize_text_field($_POST['token']);
        $status = intval($_POST['status']);
        $wpdb->insert($table_name, compact('nome', 'token', 'status'));
    }

    // Iniciar a saída do HTML
    ob_start();
    ?>
    <div class="wrap">
        <h1>Administração de Geiser</h1>
        <h2>Cadastrar Leitor</h2>
        <form method="post">
            <input type="hidden" name="action" value="add">
            <table>
                <tr>
                    <td><label for="nome">Nome:</label></td>
                    <td><input type="text" name="nome" required></td>
                </tr>
                <tr>
                    <td><label for="token">Token:</label></td>
                    <td><input type="text" name="token" required></td>
                </tr>
                <tr>
                    <td><label for="status">Status:</label></td>
                    <td><input type="checkbox" name="status" value="1"></td>
                </tr>
                <tr>
                    <td colspan="2"><input type="submit" value="Adicionar"></td>
                </tr>
            </table>
        </form>
    </div>
    <?php
    // Encerrar a saída do HTML
    echo ob_get_clean();
}



function geiser_dispositivos_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'geiser_leitores';
    $leitores = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);

    // Iniciar a saída do HTML
    ob_start();
    ?>
    <div class="wrap">
        <h1>Dispositivos Geiser</h1>
        <div class="geiser-dispositivos-container" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); grid-gap: 1em;">
            <?php foreach ($leitores as $leitor): ?>
                <div class="geiser-dispositivo-card" style="padding: 1em; border: 1px solid #ccc; border-radius: 4px; background-color: #f9f9f9;">
                    <h2 style="margin-top: 0;"><?php echo esc_html($leitor['nome']); ?></h2>
                    <p><strong>ID:</strong> <?php echo intval($leitor['id']); ?></p>
                    <p><strong>Token:</strong> <?php echo esc_html($leitor['token']); ?></p>
                    <p><strong>Status:</strong> <?php echo intval($leitor['status']) ? 'Ativo' : 'Inativo'; ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
    // Imprimir a saída do HTML
    echo ob_get_clean();
}



function geiser_logs_page() {
    global $wpdb;

    $table_name = $wpdb->prefix . "geiser_logs";
    $logs = $wpdb->get_results("SELECT * FROM $table_name  order by lastdt desc");

    echo '<h1>Demonstrativo de Armazenamento na Nuvem</h1>';

    // Adicionar estilos CSS
    echo '<style>
        table.geiser-logs-table {
            width: 100%;
            border-collapse: collapse;
        }
        table.geiser-logs-table th,
        table.geiser-logs-table td {
            padding: 8px;
            text-align: left;
            border: 1px solid #ccc;
        }
        table.geiser-logs-table thead {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        table.geiser-logs-table tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>';

    echo '<table class="geiser-logs-table">';
    echo '<thead><tr><th>ID</th><th>Token</th><th>Data/Hora</th><th>USV/h</th><th>Temperatura</th><th>Umidade</th></tr></thead>';
    echo '<tbody>';

    foreach ($logs as $log) {
        echo '<tr>';
        echo '<td>' . $log->id . '</td>';
        echo '<td>' . $log->token . '</td>';
        echo '<td>' . $log->lastdt . '</td>';
        echo '<td>' . $log->usvh . '</td>';
        echo '<td>' . $log->temp . '</td>';
        echo '<td>' . $log->hum . '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
}



function geiser_analise_page() {
  echo do_shortcode(' [gvn_schart_2 id="14" table="1"]'); 
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


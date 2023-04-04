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
    $table_name = $wpdb->prefix . 'geiser_logs';

    // Configura a paginação
    $per_page = 30;
    $current_page = isset($_GET['paged']) ? intval($_GET['paged']) : 1;
    $offset = ($current_page - 1) * $per_page;

    // Busca os registros da tabela geiser_logs ordenados cronologicamente
    $logs = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY  lastdt DESC LIMIT %d OFFSET %d", $per_page, $offset), ARRAY_A);

    // Conta o total de registros na tabela geiser_logs
    $total_logs = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

    // Calcula o total de páginas
    $total_pages = ceil($total_logs / $per_page);

    // Iniciar a saída do HTML
    ob_start();
    ?>
    <style>
        .geiser-table {
            border-collapse: collapse;
            width: 100%;
        }
        .geiser-table th, .geiser-table td {
            border: 1px solid #ccc;
            padding: 5px;
            text-align: left;
        }
        .geiser-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
    </style>
    <script>
        setTimeout(function() {
            location.reload();
        }, 5000);
    </script>
    <div class="wrap">
        <h1>Geiser Logs</h1>
        <table class="geiser-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Token</th>
                    <th>Data/Hora</th>
                    <th>usv/h</th>
                    <th>Temperatura</th>
                    <th>Humidade</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?php echo intval($log['id']); ?></td>
                        <td><?php echo intval($log['token']); ?></td>
                        <td><?php echo esc_html($log['lastdt']); ?></td>
                        <td><?php echo esc_html($log['usvh']); ?></td>
                        <td><?php echo esc_html($log['temp']); ?></td>
                        <td><?php echo esc_html($log['hum']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="tablenav">
            <div class="tablenav-pages">
                <span class="displaying-num"><?php echo $total_logs; ?> itens</span>
                <?php
                echo paginate_links(array(
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '',
                    'prev_text' => __('&laquo;'),
                    'next_text' => __('&raquo;'),
                    'total' => $total_pages,
                    'current' => $current_page
                ));
                ?>
            </div>
        </div>
    </div>
    <?php
    // Encerrar a saída do HTML
    echo ob_get_clean();
}


function geiser_analise_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'geiser_logs';

    // Obtenha os dados do banco de dados
    $logs = $wpdb->get_results("SELECT token, TIMESTAMP(lastdt) as datetime, AVG(usvh) as avg_usvh, AVG(temp) as avg_temp, AVG(hum) as avg_hum FROM $table_name GROUP BY token, UNIX_TIMESTAMP(lastdt) DIV (15 * 60) ORDER BY token, datetime", ARRAY_A);

    // Iniciar a saída do HTML
    ob_start();
    ?>
    <div class="wrap">
        <h1>Geiser Análise</h1>
        <div>
            <canvas id="geiserChart"></canvas>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Organiza os dados para o gráfico
                var logs = <?php echo json_encode($logs); ?>;
                var tokens = [];
                var data = {
                    usvh: {},
                    temp: {},
                    hum: {}
                };

                logs.forEach(function (log) {
                    if (!data.usvh.hasOwnProperty(log.token)) {
                        data.usvh[log.token] = [];
                        data.temp[log.token] = [];
                        data.hum[log.token] = [];
                    }
                    data.usvh[log.token].push({x: log.datetime, y: parseFloat(log.avg_usvh)});
                    data.temp[log.token].push({x: log.datetime, y: parseFloat(log.avg_temp)});
                    data.hum[log.token].push({x: log.datetime, y: parseFloat(log.avg_hum)});
                });

                Object.keys(data.usvh).forEach(function (key) {
                    tokens.push({
                        label: 'Token ' + key + ' - usvh',
                        data: data.usvh[key],
                        fill: false,
                        borderColor: 'rgb(75, 192, 192)',
                        tension: 0.1
                    });
                    tokens.push({
                        label: 'Token ' + key + ' - temp',
                        data: data.temp[key],
                        fill: false,
                        borderColor: 'rgb(255, 99, 132)',
                        tension: 0.1
                    });
                    tokens.push({
                        label: 'Token ' + key + ' - hum',
                        data: data.hum[key],
                        fill: false,
                        borderColor: 'rgb(255, 205, 86)',
                        tension: 0.1
                    });
                });

                // Cria o gráfico
                var ctx = document.getElementById('geiserChart').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        datasets: tokens
                    },
                    options: {
                        scales: {
                            x: {
                                type: 'time',
                                time: {
                                    unit: 'minute',
                                    displayFormats: {
                                        minute: 'HH:mm'
                                    }
                                }
                            }
                        }
                    }
                });
            });
        </script>
    </div>
    <?php
    // Encerrar a saída do HTML
    echo ob_get_clean();
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


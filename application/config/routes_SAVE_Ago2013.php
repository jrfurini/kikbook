<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There area two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router what URI segments to use if those provided
| in the URL cannot be matched to a valid route.
|
*/

$route[ 'default_controller'			]	= "jx/home/index";
$route[ '404_override'				]	= '';
$route[ 'undefined'				]	= 'jx/home/index';

/*
 * Jarvix Plus
 */
// MAIN
$route[ 'main(.*)'				]	= "jx/home$1";
$route[ 'home(.*)'				]	= "jx/home$1";
$route[ 'login'					]	= "jx/home/login";
$route[ 'facebook_login'			]	= "jx/home/facebook_login";
$route[ 'do_login'				]	= "jx/home/do_login";
$route[ 'logout'				]	= "jx/home/logout";
$route[ 'cancel'				]	= "jx/home/cancel";
$route[ 'cancelar'				]	= "jx/home/cancel";
$route[ 'channel'				]	= "jx/home/channel";
$route[ 'canvas'				]	= "jx/home/canvas";
$route[ 'robot'					]	= "jx/home/robot";
$route[ 'privacy'				]	= "regra/privacy";
$route[ 'privacidade'				]	= "regra/privacy";
$route[ 'terms'					]	= "regra/terms";
$route[ 'termos'				]	= "regra/terms";
$route[ 'termos_de_uso'				]	= "regra/terms";
$route[ 'rules'					]	= "regra/rules";
$route[ 'regras'				]	= "regra/rules";
$route[ 'support(.*)'				]	= "regra/support";
$route[ 'suporte(.*)'				]	= "regra/support";
$route[ 'regra'					]	= "regra/rules";


// Imagem
$route[ 'image/(.*)'				]	= "imagem/show_file/$1";
$route[ 'picture/(.*)'				]	= "imagem/show_file/$1";

// Agenda
$route[ 'admin_kik(.*)'				]	= "admin_kik$1";

// Treino

// Avaliação
$route[ 'avaliacao(.*)'				]	= "evaluation$1";

// JMail
$route[ 'jmail(.*)'				]	= "jmail$1";

// Marketing
$route[ 'marketing(.*)'				]	= "marketing$1";

// Finanças
$route[ 'financas(.*)'				]	= "finance$1";

// Cadastro
$route[ 'cadastro(.*)'				]	= "cad$1";

// Controles JX
$route[ 'admin(.*)'				]	= "jx/admin$1";
$route[ 'administrador(.*)'			]	= "jx/admin$1";
$route[ 'administrator(.*)'			]	= "jx/admin$1";
$route[ 'auth_ctl'				]	= "jx/auth_ctl";
$route[ 'user(.*)'				]	= "jx/user$1";
$route[ 'user_cfg(.*)'				]	= "jx/user_cfg$1";
$route[ 'esqueci_senha(.*)'			]	= 'jx/user/reminder_password$1';
$route[ 'criar_conta(.*)'			]	= 'jx/user/create_by_page$1';
$route[ 'navegacao(.*)'				]	= "jx/user_nav$1";
$route[ 'user_nav(.*)'				]	= "jx/user_nav$1";
$route[ 'sistema_ctrl(.*)'			]	= "jx/sistema_ctrl$1";
$route[ 'sistema(.*)'				]	= "jx/sistema$1";
$route[ 'sistema_ctrl_meth(.*)'			]	= "jx/sistema_ctrl_meth$1";
$route[ 'perfil(.*)'				]	= "jx/profile$1";
$route[ 'profile(.*)'				]	= "jx/profile$1";
$route[ 'user_profile(.*)'			]	= "jx/user_profile$1";
$route[ 'perfil_usuario(.*)'			]	= "jx/user_profile$1";
$route[ 'imagem(.*)'				]	= "jx/imagem$1";
$route[ 'image(.*)'				]	= "jx/imagem$1";
$route[ 'process(.*)'				]	= "jx/process$1";
$route[ 'ad_admin(.*)'				]	= "jx/ad_admin$1";
$route[ 'anunciante(.*)'			]	= "jx/anunciante$1";
$route[ 'anuncio(.*)'				]	= "jx/anuncio$1";
$route[ 'area_anuncio(.*)'			]	= "jx/area_anuncio$1";


/* End of file routes.php */
/* Location: ./application/config/routes.php */
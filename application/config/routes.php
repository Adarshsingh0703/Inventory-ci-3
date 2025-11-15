<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|-------------------------------------------------------------------
| App Routes
|-------------------------------------------------------------------
*/
$route['default_controller'] = 'auth/login';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

/* -------- Auth -------- */
$route['login']    = 'auth/login';
$route['logout']   = 'auth/logout';
$route['register'] = 'auth/register';

/* -------- Dashboard -------- */
$route['dashboard'] = 'dashboard/index';

/* Dashboard metrics JSON (AJAX) */
$route['api/dashboard_metrics'] = 'dashboard/api_metrics';

/* -------- UI: All Items & All Categories (new pages) -------- */
$route['all-items'] = 'items_page/index';
$route['all-categories'] = 'categories_page/index';

/* -------- Web CRUD (Controllers) -------- */
$route['categories']               = 'categories/index';
$route['categories/create']        = 'categories/create';
$route['categories/edit/(:num)']   = 'categories/edit/$1';
$route['categories/delete/(:num)'] = 'categories/delete/$1';

$route['items']                    = 'items/index';
$route['items/create']             = 'items/create';
$route['items/edit/(:num)']        = 'items/edit/$1';
$route['items/delete/(:num)']      = 'items/delete/$1';

/* -------- JSON API: Items -------- */
$route['api/items']        = 'api/items_api/router';
$route['api/items/(:num)'] = 'api/items_api/router/$1';

/* -------- JSON API: Categories -------- */
$route['api/categories']        = 'api/categories_api/router';
$route['api/categories/(:num)'] = 'api/categories_api/router/$1';

/* -------- JSON API: Audit Logs -------- */
$route['api/audit']        = 'api/audit_api/router';
$route['api/audit/(:num)'] = 'api/audit_api/router/$1';

/* (Optional) UI route for an Audit Logs page */
$route['audit'] = 'audit/index';

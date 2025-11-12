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

/* -------- Web CRUD (Controllers) -------- */
$route['categories']               = 'categories/index';
$route['categories/create']        = 'categories/create';
$route['categories/edit/(:num)']   = 'categories/edit/$1';
$route['categories/delete/(:num)'] = 'categories/delete/$1';

$route['items']                    = 'items/index';
$route['items/create']             = 'items/create';
$route['items/edit/(:num)']        = 'items/edit/$1';
$route['items/delete/(:num)']      = 'items/delete/$1';

/* -------- JSON API: Items --------
   Uses a single router method with _method override for PUT/DELETE.
*/
$route['api/items']        = 'api/items_api/router';
$route['api/items/(:num)'] = 'api/items_api/router/$1';

/* -------- JSON API: Categories -------- */
$route['api/categories']        = 'api/categories_api/router';
$route['api/categories/(:num)'] = 'api/categories_api/router/$1';

/* -------- JSON API: Audit Logs --------
   Endpoints:
     GET    /api/audit
     GET    /api/audit/{id}
     POST   /api/audit                      (create manual log)
     POST   /api/audit/{id} + _method=DELETE (delete a log)
*/
$route['api/audit']        = 'api/audit_api/router';
$route['api/audit/(:num)'] = 'api/audit_api/router/$1';

/* (Optional) UI route for an Audit Logs page we'll add next */
$route['audit'] = 'audit/index';

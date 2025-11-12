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
   One central router handles methods via _method override.
   Examples:
     GET    /index.php/api/items
     GET    /index.php/api/items/123
     POST   /index.php/api/items                 (create)
     POST   /index.php/api/items/123 + _method=PUT    (update)
     POST   /index.php/api/items/123 + _method=DELETE (delete)
*/
$route['api/items']        = 'api/items_api/router';
$route['api/items/(:num)'] = 'api/items_api/router/$1';

/* -------- JSON API: Categories --------
   Same pattern as Items.
   Examples:
     GET    /index.php/api/categories
     GET    /index.php/api/categories/5
     POST   /index.php/api/categories                 (create)
     POST   /index.php/api/categories/5 + _method=PUT    (update)
     POST   /index.php/api/categories/5 + _method=DELETE (delete)
*/
$route['api/categories']        = 'api/categories_api/router';
$route['api/categories/(:num)'] = 'api/categories_api/router/$1';

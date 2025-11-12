<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|-------------------------------------------------------------------
| Base Site URL
|-------------------------------------------------------------------
*/
$config['base_url'] = 'http://localhost:8082/';

/*
|-------------------------------------------------------------------
| Index File
|-------------------------------------------------------------------
*/
$config['index_page'] = '';

/*
|-------------------------------------------------------------------
| URI Protocol
|-------------------------------------------------------------------
*/
$config['uri_protocol'] = 'REQUEST_URI';

/*
|-------------------------------------------------------------------
| Default Language and Charset
|-------------------------------------------------------------------
*/
$config['language'] = 'english';
$config['charset']  = 'UTF-8';

/*
|-------------------------------------------------------------------
| Enable/Disable System Hooks
|-------------------------------------------------------------------
*/
$config['enable_hooks'] = FALSE;

/*
|-------------------------------------------------------------------
| Class Extension Prefix
|-------------------------------------------------------------------
*/
$config['subclass_prefix'] = 'MY_';

/*
|-------------------------------------------------------------------
| Composer Autoload
|-------------------------------------------------------------------
*/
$config['composer_autoload'] = FALSE;

/*
|-------------------------------------------------------------------
| Error Logging
|-------------------------------------------------------------------
*/
$config['log_threshold']        = 1;
$config['log_path']             = APPPATH.'logs/';
$config['log_file_extension']   = '';
$config['log_file_permissions'] = 0644;
$config['log_date_format']      = 'Y-m-d H:i:s';

/*
|-------------------------------------------------------------------
| Encryption Key
|-------------------------------------------------------------------
*/
$config['encryption_key'] = 'abfrgdj3456ry7fjdy873hfr231fjtnc';

/*
|-------------------------------------------------------------------
| Session Settings
|-------------------------------------------------------------------
*/
$config['sess_driver']             = 'files';
$config['sess_cookie_name']        = 'ci_session';
$config['sess_expiration']         = 7200;
$config['sess_save_path']          = APPPATH.'sessions';
$config['sess_match_ip']           = FALSE;
$config['sess_time_to_update']     = 300;
$config['sess_regenerate_destroy'] = FALSE;

/*
|-------------------------------------------------------------------
| Cookie Settings
|-------------------------------------------------------------------
*/
$config['cookie_prefix']   = '';
$config['cookie_domain']   = '';
$config['cookie_path']     = '/';
$config['cookie_secure']   = FALSE;
$config['cookie_httponly'] = FALSE;
$config['cookie_samesite'] = 'Lax';

/*
|-------------------------------------------------------------------
| Security
|-------------------------------------------------------------------
*/
$config['csrf_protection']   = FALSE; // enable later if needed
$config['global_xss_filtering'] = FALSE;

/*
|-------------------------------------------------------------------
| Other basic settings
|-------------------------------------------------------------------
*/
$config['index_page'] = '';
$config['url_suffix'] = '';
$config['permitted_uri_chars'] = 'a-z 0-9~%.:_\-';
$config['allow_get_array'] = TRUE;
$config['enable_query_strings'] = FALSE;
$config['controller_trigger'] = 'c';
$config['function_trigger']   = 'm';
$config['directory_trigger']  = 'd';
$config['compress_output'] = FALSE;
$config['time_reference']  = 'local';
$config['rewrite_short_tags'] = FALSE;
$config['proxy_ips'] = '';

<?php
class AuthLogin
{
    var $ok;
    var $salt = 'as5d64f65';
    var $domain = 'localhost';
    var $config_db = './assets/db/config.json';

    public function auth()
    {
        $this->ok = false;

        if (!$this->check_session())
            $this->check_cookie();

        return $this->ok;
    }

    public function login($password)
    {
        if ($this->check(md5($password . $this->salt)))
        {
            $this->ok = true;
            $_SESSION['password'] = md5($password . $this->salt);
            setcookie("password", md5($password . $this->salt), time() + 60 * 60 * 24 * 30, "/", $this->domain);

            return true;
        }
        else
        {
            return false;
        }
    }

    public function logout()
    {
        $this->ok = false;

        $_SESSION['password'] = "";
        setcookie("password", "", time() - 3600, "/", $this->domain);
    }

    private function check_session()
    {
        if (!empty($_SESSION['password']))
            return $this->check($_SESSION['password']);
        else
            return false;
    }

    private function check_cookie()
    {
        if (!empty($_COOKIE['password']))
            return $this->check($_COOKIE['password']);
        else
            return false;
    }

    private function check($password_md5)
    {
        $admin_password = md5(json_decode(file_get_contents($this->config_db),true)['admin_pass'] . $this->salt);

        if ($admin_password == $password_md5)
        {
            $this->ok = true;
            return true;
        }
        else
        {
            return false;
        }
    }
}

/**
 * 生成永远唯一的密钥码
 * sha512(返回128位) sha384(返回96位) sha256(返回64位) md5(返回32位)
 * 还有很多Hash函数......
 * @author xiaochaun
 * @param int $type 返回格式：0大小写混合  1全大写  2全小写
 * @param string $func 启用算法：                
 * @return string
 */
function create_secret($type = 0, $func = 'sha512')
{
    $uid = md5(uniqid(rand(), true) . microtime());
    $hash = hash($func, $uid);
    $arr = str_split($hash);
    foreach ($arr as $v)
    {
        if ($type == 0)
        {
            $newArr[] = empty(rand(0, 1)) ? strtoupper($v) : $v;
        }
        if ($type == 1)
        {
            $newArr[] = strtoupper($v);
        }
        if ($type == 2)
        {
            $newArr[] = $v;
        }
    }
    return implode('', $newArr);
}

$account_db = './assets/db/account.json';
$log_db = './assets/db/log.json';
$config_db = './assets/db/config.json';

if (isset($_GET['api']))
{

    $account_arr = json_decode(file_get_contents($account_db), true);
    switch ($_GET['api'])
    {
        case 'add_account':
            /*
            #register-Old version
            $log_arr=json_decode(file_get_contents($log_db),true);
            if($_POST['id']!='' && $_POST['pw']!='')
            {
                $account_arr[]=array('id'=>$_POST['id'],'pw'=>$_POST['pw'],'stat'=>true);
                file_put_contents($account_db,json_encode($account_arr));
                $log_arr[$_POST['id']]=array();
                file_put_contents($log_db,json_encode($log_arr));
                echo json_encode(array("title"=>"success","content"=>'add_account_success'));
            }
            else
                echo json_encode(array("title"=>"err","content"=>'add_account_fail'));
            break;
            */
            #register-New version with auth-code
            $log_arr = json_decode(file_get_contents($log_db), true);
            $config_arr = json_decode(file_get_contents($config_db), true);
            if ($_POST['id'] != '' && $_POST['pw'] != '' && $_POST['auth_code'] != '')
            {
                for ($i = 0; $i < count($config_arr['auth_code']); $i++)
                {
                    if ($config_arr['auth_code'][$i]['code'] == $_POST['auth_code'] && $config_arr['auth_code'][$i]['user'] == null)
                    {
                        $account_arr[] = array('id' => $_POST['id'], 'pw' => $_POST['pw'], 'stat' => true);
                        file_put_contents($account_db, json_encode($account_arr));
                        $log_arr[$_POST['id']] = array();
                        file_put_contents($log_db, json_encode($log_arr));
                        $config_arr['auth_code'][$i]['user'] = $_POST['id'];
                        file_put_contents($config_db, json_encode($config_arr));
                        echo json_encode(array("title" => "success", "content" => 'add_account_success'));
                        die();
                    }
                }
                echo json_encode(array("title" => "err", "content" => 'add_account_fail_invalid_auth_key'));
            }
            else
            {
                echo json_encode(array("title" => "err", "content" => 'add_account_fail_invalid_blank_value'));
            }
            break;


        case 'del_account':
            for ($i = 0; $i < count($account_arr); $i++)
                if ($account_arr[$i]['id'] == $_POST['id'])
                    array_splice($account_arr, $i, 1);
            file_put_contents($account_db, json_encode($account_arr));
            echo json_encode(array("title" => "success", "content" => 'delete_account_success'));
            break;

        case 'show_account':
            echo file_get_contents($account_db);
            break;

        case 'show_log':
            echo json_encode(json_decode(file_get_contents($log_db), true)[$_POST['id']]['log']);
            break;

        case 'check_in':
            $log_arr = json_decode(file_get_contents($log_db), true);
            $flag = 0;
            foreach ($account_arr as $item)
            {
                if ($item['id'] == $_POST['id'])
                {
                    $flag = 1;
                    if ($item['stat'] != false)
                    {
                        $id = $item['id'];
                        $pw = $item['pw'];
                        exec("./main.py --id $id --pw $pw", $content);
                        $log_arr[$id]['log'][] = array('date' => date("Y-m-d H:i:s"), 'content' => $content[0]);
                        $res = 'check_in_success';
                    }
                    else
                    {
                        $id = $item['id'];
                        $log_arr[$id]['log'][] = array('date' => date("Y-m-d H:i:s"), 'content' => '你未启用自动打卡');
                        $res = 'you_not_enable_service';
                    }
                    break;
                }
            }
            file_put_contents($log_db, json_encode($log_arr));
            echo $flag ? json_encode(array("title" => "success", "content" => $res)) : json_encode(array("title" => "err", "content" => 'no_such_person'));
            break;

        case 'switch_stat':
            for ($i = 0; $i < count($account_arr); $i++)
                if ($account_arr[$i]['id'] == $_POST['id'])
                    if ($account_arr[$i]['pw'] == $_POST['pw'])
                        $res = $account_arr[$i]['stat'] = ($account_arr[$i]['stat'] == false ? true : false);
            file_put_contents($account_db, json_encode($account_arr));
            echo json_encode(array("title" => "success", "content" => 'switch_status_success', 'data' => $res));
            break;

        case 'stat_get':
            $flag = 0;
            $res = '2';
            for ($i = 0; $i < count($account_arr); $i++)
                if ($account_arr[$i]['id'] == $_POST['id'])
                {
                    $flag = 1;
                    $res = $account_arr[$i]['stat'];
                }
            echo $flag ? json_encode(array("title" => "success", "content" => 'get_status_success', 'data' => $res)) : json_encode(array("title" => "err", "content" => 'no_such_person', 'data' => $res));
            break;

        default:
            echo json_encode(array("title" => "err", "content" => "no_match_api_name"));
            break;
    }
}
else if (isset($_GET['admin']))
{
    $admin = new AuthLogin;

    session_start();
    switch ($_GET['admin'])
    {
        case 'login':
            if ($admin->login($_POST['pw']))
                echo json_encode(array('title' => 'success', 'content' => 'login_success', 'stat' => $admin->auth()));
            break;

        case 'get_stat':
            echo json_encode(array('title' => 'success', 'content' => 'get_stat_success', 'stat' => $admin->auth()));
            break;

        case 'check_in':
            $account_arr = json_decode(file_get_contents($account_db), true);
            $log_arr = json_decode(file_get_contents($log_db), true);
            $config = json_decode(file_get_contents($config_db), true);
            if ($config['status'] == true)
            {
                foreach ($account_arr as $item)
                {
                    if ($item['stat'] != false)
                    {
                        $id = $item['id'];
                        $pw = $item['pw'];
                        exec("./main.py --id $id --pw $pw", $content);
                        $log_arr[$id]['log'][] = array('date' => date("Y-m-d H:i:s"), 'content' => $content[0]);
                    }
                    else
                    {
                        $id = $item['id'];
                        $log_arr[$id]['log'][] = array('date' => date("Y-m-d H:i:s"), 'content' => '你未启用自动打卡');
                    }
                }
                $config['log'][] = array('date' => date("Y-m-d H:i:s"), 'content' => 'check_in_success');
                file_put_contents($log_db, json_encode($log_arr));
                echo json_encode(array("title" => "success", "content" => 'check_in_success'));
            }
            else
            {
                $config['log'][] = array('date' => date("Y-m-d H:i:s"), 'content' => 'admin_disabled_auto_check_in_service');
                echo json_encode(array("title" => "err", "content" => 'admin_disabled_auto_check_in_service'));
            }
            file_put_contents($config_db, json_encode($config));
            break;

        default:
            if ($admin->auth())
                switch ($_GET['admin'])
                {
                    case 'logout':
                        $admin->logout();
                        echo json_encode(array('title' => 'success', 'content' => 'logout_success', 'stat' => false));
                        break;

                    case 'glance':
                        $data = array('enabled' => 0, 'disabled' => 0);
                        foreach (json_decode(file_get_contents($account_db), true) as $user)
                        {
                            if ($user['stat'] == true)
                                $data['enabled']++;
                            else
                                $data['disabled']++;
                        }
                        $log = json_decode(file_get_contents($config_db), true)['log'];
                        $data['check_in_count'] = 0;
                        foreach ($log as $item)
                        {
                            if (strtotime($item['date']) >= strtotime(date("Y-m-d")))
                                $data['check_in_count']++;
                        }
                        echo json_encode($data);
                        break;

                    case 'auth':
                        $data = array('auth_code' => json_decode(file_get_contents($config_db), true)['auth_code'], 'available' => 0, 'unavailable' => 0);
                        foreach ($data['auth_code'] as $item)
                            if ($item['user'] == null)
                                $data['available']++;
                            else
                                $data['unavailable']++;
                        echo json_encode($data);
                        break;

                    case 'get_service_log':
                        echo json_encode(json_decode(file_get_contents($config_db), true)['log']);
                        break;

                    case 'gen_auth_code':
                        $config = json_decode(file_get_contents($config_db), true);
                        $code = create_secret($type = 0, $func = 'md5');
                        $config['auth_code'][] = array('code' => $code, 'user' => null);
                        file_put_contents($config_db, json_encode($config));
                        echo $code;
                        break;

                    case 'get_service_stat':
                        echo json_encode(json_decode(file_get_contents($config_db), true)['status']);
                        break;

                    case 'switch_stat':
                        $config = json_decode(file_get_contents($config_db), true);
                        $config['status'] = $config['status'] == true ? false : true;
                        file_put_contents($config_db, json_encode($config));
                        echo json_encode(array('title' => 'success', 'content' => 'switch_status_success', 'stat' => $config['status']));
                }
            else
                echo json_encode(array('title' => 'err', 'content' => 'not_login', 'stat' => false));
    }
}

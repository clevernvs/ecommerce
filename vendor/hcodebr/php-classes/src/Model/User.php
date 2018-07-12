<?php
namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

/*
    ::getFromSession()
    ::checkLogin()
    ::login()
    ::verifyLogin()
    ::logout()
    ::listAll()
    save()
    get()
    update()
    delete()
    ::getForgot()
    ::validForgotDecrypt()
    ::setForgotUsed()
    setPassword()
    ::setError()
    ::getError()
    ::clearError()
    ::setSuccess()
    ::getSuccess()
    ::clearSuccess()
    ::setErrorRegister()
    ::getErrorRegister()
    ::clearErrorRegister()
    ::checkLoginExist()
    getPasswordHash()
 */

class User extends Model
{
    const SESSION = "User";
    const SECRET = "HcodePhp7_Secret";
    const ERROR = "UserError";
    const ERROR_REGISTER = "UserErrorRegister";
    const SUCCESS = "UserSuccess";

    public static function getFromSession()
    {
        $user = new User();

        if (isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]['iduser'] > 0 ) {

            $user->setData($_SESSION[User::SESSION]);           

        }

        return $user;
    }

    public static function checkLogin($inadmin = true)
    {
        if (!isset($_SESSION[User::SESSION])
            || !$_SESSION[User::SESSION]
            || !(int)$_SESSION[User::SESSION]["iduser"] > 0
        ) {
            // Não está logado
            return false;
        } else {
            
            if ($inadmin === true && (bool)$_SESSION[User::SESSION]['inadmin'] === true) {
                return true;
            } else if ($inadmin === false) {
                return true;
            } else {
                return false;
            }            
        }
        
    }

    public static function login($login, $password)
    {
        // Conectar com o BD
        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
            ":LOGIN"=>$login,
        ));

        if (count($results) === 0) {
            throw new \Exception("Usuário inexistente ou senha inválida.");
        }

        $data = $results[0];

        if (password_verify($password, $data["despassword"]) === true) {

            $user = new User();

            $data['desperson'] = utf8_encode($data['desperson']);

            $user->setData($data);

            $_SESSION[User::SESSION] = $user->getValues();

            return $user;

        } else {
            throw new \Exception("Usuário inexistente ou senha inválida.");
        }

    }

    public static function verifyLogin($inadmin = true)
    {
        if (User::checkLogin($inadmin)) {
            
            header("Location: /admin/login");
            exit;
        }
    }

    public static function logout($value='')
    {
        $_SESSION[User::SESSION] = NULL;
    }

    public static function listAll()
    {
        // Conectar com o BD
        $sql = new Sql();
        return $sql->select("SELECT * FROM tb_users INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");
    }

    public function save()
    {
        // Conectar com o BD
        $sql = new Sql();

        $results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desmail, :nrphone, :inadmin)", array(
            ":desperson"    => utf8_decode($this->getdesperson()),
            ":deslogin"     => $this->getdeslogin(),
            ":despassword"  => User::getPasswordHash($this->getdespassword()),
            ":desmail"      => $this->getdesemail(),
            ":nrphone"      => $this->getdesphone(),
            ":inadmin"      => $this->getdesinadmin(),
        ));

        $this->setData($results[0]);
    }

    public function get($iduser)
    {
        // Conectar com o BD
        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser", array(
            ":iduser"=>iduser
        ));
        
        $data = $results[0];
        $data['desperson'] = utf8_encode($data['desperson']);

        $this->setData(results[0]);
    }

    public function update()
    {
        // Conectar com o BD
        $sql = new Sql();

        $results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desmail, :nrphone, :inadmin)", array(
            ":iduser"       => $this->getiduser(),
            ":desperson"    => utf8_decode($this->getdesperson()),
            ":deslogin"     => $this->getdeslogin(),
            ":despassword"  => User::getPasswordHash($this->getdespassword()),
            ":desmail"      => $this->getdesemail(),
            ":nrphone"      => $this->getdesphone(),
            ":inadmin"      => $this->getdesinadmin(),
        ));

        $this->setData($results[0]);
    }

    public function delete()
    {
        // Conectar com o BD
        $sql = new Sql();

        $results = $sql->select("CALL sp_users_delete(:iduser)", array(
            ":iduser"=>$this->getiduser()
        ));
    }


    // Enviar e-mail para recuperação
    public static function getForgot($email, $inadmin = true)
    {   
        // Conectar com o BD
        $sql = new Sql();

        $results = $sql->select("
            SELECT *
            FROM tb_persons a
            INNER JOIN tb_users b USING(idperson)
            WHERE a.desemail = :email
        ", array(
            ":email"=>$email
        ));

        if (count($results) === 0) {
            throw new \Exception("Não foi possível recuperar a senha.");
        } else {

            $data = $results[0];

            $results2 = $sql->select("CALL sp_userspasswordrecoveries_create(:iduser, :desip)", array(
                ":iduser"=>$data["iduser"],
                ":desip"=>$_SERVER["REMOTE_ADDR"]
            ));

            if (count($results2) === 0) {
                throw new \Exception("Não foi possível recuperar a senha.", 1);
            } else {

                $dataRecovery = $results2[0];

                $code = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, User::SECRET, $dataRecovery["idrecovery"], MCRYPT_MODE_ECB));

                // Enviar o link caso ADMIN senão USER
                if ($inadmin === true) {
                    // ADMIN
                    $link = "http://www.hcodecommerce.com.br/admin/forgot/reset?code=$code";                    
                } else {
                    //USER
                    $link = "http://www.hcodecommerce.com.br/forgot/reset?code=$code"; 
                }

                // Enviar o e-mail
                $mailer = new Mailer($data["desemail"], $data["desperson"], "Redefinir Senha da Hcode Store", "forgot", array(
                    "name"=>$data["desperson"],
                    "link"=>$link
                )) ;
                $mailer->send();

                return $data;
            }

        }

    }

    public static function validForgotDecrypt($code)
    {         
        $idrecovery = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, User::SECRET, base64_decode($code), MCRYPT_MODE_ECB);

        // Conectar com o BD
        $sql = new Sql();
        $results = $sql->select("
            SELECT * 
            FROM tb_userspasswordsrecoveries a
            INNER JOIN tb_users b USING(iduser)
            INNER JOIN tb_persons c USING(idperson)
            WHERE a.idrecovery = :idrecovery
                AND a.dtrecovery IS NULL
                AND DATE ADD (a.dtregister, INTERVAL 1 HOUR) >= NOW()
        ", array(
            ":idrecovery"=>$idrecovery
        ));

        if (count($results) === 0) {
            throw new \Exception("Não foi possível recuperar a senha.", 1);          
        } else {
            return $results[0];
        }

    }

    public static function setForgotUsed($idrecovery)
    {
        // Conectar com o BD
        $sql = new Sql();
        $sql->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() WHERE idrecovery = :idrecovery", array(
            ":idrecovery"=>$idrecovery
        ));
    }

    public function setPassword($password)
    {
        // Conectar com o BD
        $sql = new Sql();
        $sql->query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser", array(
            ":password"=>$password,
            ":iduser"=>$this->getiduser()
        ));
    }

    public static function setError($msg)
    {
        $_SESSION[User::ERROR] = $msg;
    }

    public static function getError()
    {
        $msg = (isset($_SESSION[User::ERROR]) && $_SESSION[User::ERROR]) ? $_SESSION[User::ERROR] : '';

        User::clearError();

        return $msg;
    }

    public static function clearError()
    {
        $_SESSION[User::ERROR] = null;
    }

    public static function setSuccess($msg)
    {
        $_SESSION[User::SUCCESS] = $msg;
    }

    public static function getSuccess()
    {
        $msg = (isset($_SESSION[User::SUCCESS]) && $_SESSION[User::SUCCESS]) ? $_SESSION[User::SUCCESS] : '';

        User::clearSuccess();

        return $msg;
    }

    public static function clearSuccess()
    {
        $_SESSION[User::SUCCESS] = null;
    }

    public static function setErrorRegister($msg)
    {
        $_SESSION[User::ERROR_REGISTER] = $msg;
    }

    public static function getErrorRegister()
    {
        $msg = (isset($_SESSION[User::ERROR_REGISTER]) && $_SESSION[User::ERROR_REGISTER]) ? $_SESSION[User::ERROR_REGISTER] : '';

        User::clearErrorRegister();

        return $msg;
    }

    public static function clearErrorRegister()
    {
        $_SESSION[User::ERROR_REGISTER] = NULL;
    }

    public static function checkLoginExist($login)
    {
        // Conectar com o BD
        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :deslogin", [
            'deslogin' => $login
        ]);

        return (count($results) > 0);
    }

    public static function getPasswordHash($password)
    {
        return password_hash($password,PASSWORD_DEFAULT,[
            'cost' => 12
        ]);
    }

    public function getOrders($idorder)
    {
        // Conectar com o BD
        $sql = new Sql();

        $results = $sql->select(
            "SELECT * FROM tb_orders a 
            INNER JOIN tb_ordersstatus b USING(idstatus)
            INNER JOIN tb_carts c USING(idcart)
            INNER JOIN tb_users d ON d.iduser = a.iduser
            INNER JOIN tb_addresses e USING(idaddress)
            INNER JOIN tb_persons f ON f.idperson = d.idperson
            WHERE a.iduser = :iduser
            ", [
                ':iduser' => $this->getiduser()
        ]);
        
        return $results;
    }





}



?>

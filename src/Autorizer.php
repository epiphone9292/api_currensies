<?php

class Autorizer extends Connect
{
    public function checkUser($login, $pass)
    {
        $sql = 'SELECT hash FROM main.users WHERE login = :login';
        $stmt = $this->db->prepare($sql);
        $prepare = $stmt->execute(["login" => $login]);
        if ($prepare) {
            $hash = $stmt->fetch()['hash'];
        }

        return password_verify($pass, $hash);
    }

    public function createToken()
    {
        $token = md5(uniqid());
        $timeDecay = date('Y-m-d H:i:s', time() + 60*15);

        $sql = "INSERT INTO main.tokens (token, decay) VALUES ('$token', '$timeDecay')";
        $this->db->exec($sql);

        return $token;
    }

    public function checkToken($token)
    {
        $result = false;

        $sql = "SELECT decay FROM main.tokens WHERE token = :token";
        $stmt = $this->db->prepare($sql);
        $prepare = $stmt->execute(["token" => $token]);

        if ($prepare) {
            $timeDecay = $stmt->fetch()['decay'];
        }

        if ($timeDecay && $timeDecay >= date('Y-m-d H:i:s')) {
            $this->updateToken($token);
            $result = true;
        }

        return $result;
    }

    private function updateToken($token)
    {
        $timeDecay = date('Y-m-d H:i:s', time() + 60*15);

        $sql = "UPDATE main.tokens SET decay = '$timeDecay' WHERE token = :token";
        $stmt = $this->db->prepare($sql);
        $prepare = $stmt->execute(["token" => $token]);
    }
}

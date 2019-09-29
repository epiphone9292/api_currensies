<?php

class CurrenciesGetter extends Connect
{
    public function currencies($page)
    {
        $sql = 'SELECT id, name, rate
            FROM main.currency';

        if ($page >= 0) {
            $sql .= sprintf(' LIMIT 10 OFFSET %d', $page * 10);
        }

        $query = $this->db->query($sql);
        $result = $query->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }

    public function currency($id)
    {
        $result = [];
        $sql = "SELECT id, name, rate
            FROM main.currency
            WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute(["id" => $id]);

        if ($result) {
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $data;
        }
    }
}

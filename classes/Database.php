<?php

class Database
{
    private $pdo, $query, $error = false, $results=false, $count=false;
    private static $instance = null;

    private function __construct()
    {
        try {
            $this->pdo = new PDO("mysql:host=" . Config::get('mysql.host') . ";dbname=" . Config::get('mysql.database'), Config::get('mysql.username'), Config::get('mysql.password'));
        } catch (PDOException $exception) {
            echo "Невозможно установить соединение с БД" . $exception->getMessage();
        }
    }

    /**
     * Создает новый или возвращает текущий экземпляр класса Database
     *
     * @return Database|null
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    /**
     * Выполняет полученный запрос $sql с параметрами $params
     *
     * @param string $sql - строка sql запроса с псевдопеременными "?"
     * @param array $params - массив с параметрами для запроса ['param1', 'param2', ...]
     * @return Database
     */
    private function query(string $sql, $params = [])
    {
        $this->error = false;
        $this->query = $this->pdo->prepare($sql);

        if (count($params)) {
            $i = 1;
            foreach ($params as $param) {
                $this->query->bindValue($i, $param);
                $i++;
            }
        }

        if (!$this->query->execute()) {
            $this->error = true;
        } else {
            $this->results = $this->query->fetchAll(PDO::FETCH_OBJ);
            $this->count = $this->query->rowCount();
        }
        return $this;
    }

    /**
     * Выполняет запрос $action в таблице $table с параметрами $where
     *
     * @param string $action
     * @param string $table
     * @param array $where массив с выборкой н-р ['id', '=', 5]
     * @return Database|false
     */
    private function action(string $action, string $table, $where = [])
    {
        if (count($where) === 3) {

            $operators = ['<', '<=', '=', '>', '>='];

            $field = $where[0];
            $operator = $where[1];
            $value = $where[2];

            if (in_array($operator, $operators)) {
                $sql = "{$action} FROM `{$table}` WHERE {$field}  {$operator}  ?";
                if (!$this->query($sql, [$value])->error()) {
                    return $this;
                }
            }
        }
        return false;
    }

    /**
     * Записывает в таблицу $table значения из массива $fields = ['field_name'=>'new_value']
     *
     * @param string $table
     * @param array $fields
     * @return bool
     */
    public function insert($table, $fields = [])
    {
        $values = '';
        foreach ($fields as $item) {
            $values .= "?,";
        }
        $sql = "INSERT INTO {$table} (`" . implode('`, `', array_keys($fields)) . "`) VALUES (" . rtrim($values, ",") . ")";

        if (!$this->query($sql, $fields)->error()) {
            return true;
        }
        return false;
    }

    /**
     * Обновление записи в таблице $table по id = $id, значения ['field_name'=>'new_value']
     *
     * @param string $table
     * @param string $id
     * @param array $fields
     * @return bool
     */
    public function update($table, $id, $fields = [])
    {
        $sql = "UPDATE {$table} SET " . implode('=?,', array_keys($fields)) . "=? WHERE id=?";
        $fields['id'] = $id;

        if (!$this->query($sql, $fields)->error()) {
            return true;
        }
        return false;
    }

    /**
     * Получить из таблицы $table значение $where = ['field_name','operator','value']
     *
     * @param string $table
     * @param array $where
     * @return Database|false
     */
    public function get($table, $where = [])
    {
        return $this->action('SELECT *', $table, $where);
    }

    /**
     * Удалить из таблицы $table значение $where = ['field_name','operator','value']
     *
     * @param string $table
     * @param array $where
     * @return Database|false
     */
    public function delete($table, $where)
    {
        return $this->action('DELETE', $table, $where);
    }

    /**
     * Возвращает наличие ощибки при последнем запросе
     *
     * @return bool
     */
    public function error()
    {
        return $this->error;
    }

    /**
     * Возвращает ассоциативный массив из последнего запроса или false
     *
     * @return array|boolean
     */
    public function results()
    {
        return $this->results;
    }

    /**
     * Количество строк в последнем запросе
     *
     * @return int|false
     */
    public function count()
    {
        return $this->count;
    }

    /**
     * Возвращает первый элемент из массива results
     *
     * @return array|false
     */
    public function first()
    {
        if ($this->results()){
            return $this->results()[0];
        }
        return false;
    }
}

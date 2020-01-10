<?php


class DB
{
    private $connection;

    public function __construct(string $host, string $username, string $password, string $database)
    {
        $this->connection = new mysqli($host, $username, $password, $database);

        if ($this->connection->connect_error) {
            throw new \RuntimeException($this->connection->connect_error);
        }
    }

    public function getUserByUsername(string $username): ?array
    {
        $stmt = $this->connection->prepare('SELECT * FROM api_users WHERE username=?');
        $stmt->bind_param('s', $username);

        $stmt->execute();

        $result = $stmt->get_result()->fetch_assoc();

        $stmt->fetch();
        $stmt->close();

        return $result;
    }

    public function getStudents(int $page, int $perPage): ?array
    {
        $offset = ($page - 1) * $perPage;
        $stmt = $this
            ->connection
            ->prepare('SELECT students.*, groups.name as group_name FROM students JOIN groups ON students.group_id = groups.id ORDER BY id ASC LIMIT ? OFFSET ?')
        ;
        $stmt->bind_param('ii', $perPage, $offset);

        $stmt->execute();

        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        $stmt->fetch();
        $stmt->close();

        return $result;
    }

    public function getTotalStudents(): ?int
    {
        $stmt = $this->connection->prepare('SELECT count(*) as c FROM students');

        $stmt->execute();

        $result = $stmt->get_result()->fetch_row();

        $stmt->fetch();
        $stmt->close();

        return $result[0];
    }
}

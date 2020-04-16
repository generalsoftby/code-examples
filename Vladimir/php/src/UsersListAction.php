<?php

class UsersListAction extends AbstractAction
{
    public function execute(): array
    {
        if (!$this->session->isLoggedIn()) {
            return [
                'status' => 403,
            ];
        }

        $page = (int)($_GET['page'] ?? 1);
        $results = $this->db->getStudents($page, 5);
        $total = $this->db->getTotalStudents();

        return [
          'status' => 200,
          'content' => [
              'data' => $results,
              'total' => $total,
              'page' => $page,
          ],
        ];
    }
}

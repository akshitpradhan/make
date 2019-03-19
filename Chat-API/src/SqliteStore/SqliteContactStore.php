<?php

interface ContactStoreInterface
{
    public function saveContact($number, $status, $synced, $startedConv);
}

class SqliteContactStore implements ContactStoreInterface
{
    const DATA_FOLDER = 'wadata';

    private $db;

    public function __construct($number, $customPath)
    {
        $fileName = $customPath.'contacts.db';

        $createTable = !file_exists($fileName);

        $this->db = new \PDO('sqlite:'.$fileName, null, null, [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        if ($createTable) {
            $this->db->exec('CREATE TABLE contacts (`number` TEXT, `status` TEXT, synced INT, startedConv INT)');
        }
    }

    public function saveContact($number, $status, $synced, $startedConv)
    {
        $sql = 'INSERT INTO contacts (`number`, `status`, `synced`, `startedConv`) VALUES (:number, :status, :synced, :startedConv)';
        $query = $this->db->prepare($sql);

        $query->execute(
            [
                ':number'     => $number,
                ':status'     => $status,
                ':synced'     => $synced,
                'startedConv' => $startedConv,
            ]
        );
    }

    public function existContactAndSynced($number)
    {
        $query = $this->db->prepare('SELECT `number` FROM `contacts` WHERE (number = :number AND synced = :synced)');

        $query->execute(
          [
              ':number'    => $number,
              ':synced'    => 1,
          ]
      );

        if ($query->fetch()) {
            return true;
        } else {
            return false;
        }
    }

    public function existContact($number)
    {
        $query = $this->db->prepare('SELECT `number` FROM `contacts` WHERE (number = :number)');

        $query->execute(
          [
              ':number'    => $number,
          ]
      );

        if ($query->fetch()) {
            return true;
        } else {
            return false;
        }
    }

    public function setSyncedContact($number)
    {
        $query = $this->db->prepare('UPDATE contacts SET synced=1 WHERE number = :number');

        $query->execute(
          [
              ':number'    => $number,
          ]
      );
    }

    public function startedConv($number)
    {
        $query = $this->db->prepare('SELECT `number` FROM `contacts` WHERE (number = :number AND startedConv = :startedConv)');

        $query->execute(
          [
              ':number'       => $number,
              ':startedConv'  => 1,
          ]
      );

        if ($query->fetch()) {
            return true;
        } else {
            return false;
        }
    }

    public function getStatus($number)
    {
        $query = $this->db->prepare('SELECT `status` FROM `contacts` WHERE (number = :number)');

        $query->execute(
          [
              ':number'    => $number,
          ]
      );

        return $query->fetch()['status'];
    }

    public function getSyncedContacts()
    {
        $query = $this->db->prepare('SELECT `number` FROM `contacts` WHERE (synced = :synced)');

        $query->execute(
          [
              ':synced'    => 1,
          ]
      );

        $synced = [];
        foreach ($query->fetchAll() as $number) {
            $synced[] = $number['number'];
        }

        return $synced;
    }
}

<?php

declare(strict_types = 1);

namespace snare\BetterPrisons\session;

use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;
use snare\BetterPrisons\BetterPrisons;

class DataSessionManager
{
    /** @var DataConnector */
    private DataConnector $dataConnector;

    /** @var DataSession[] */
    private array $dataSessions = [];

    public function __construct()
    {
        $this->dataConnector = libasynql::create(BetterPrisons::getBetterPrisons(), BetterPrisons::getBetterPrisons()->getConfig()->get("database"), [
            "mysql" => "mysql.sql",
            "sqlite" => "sqlite.sql"
        ]);

        $this->dataConnector->executeGeneric("table.users");
        $this->dataConnector->waitAll();
        $this->loadData();
    }

    private function loadData() : void
    {
        $this->dataConnector->executeSelect("data.users.getAll", [], function (array $rows) : void {
            foreach ($rows as $row) {
                $this->dataSessions[strtolower($row["name"])] = new DataSession($row["name"], $row["rank"], $row["prestige"], $row["blocksBroken"]);
            }
        });
    }

    /**
     * @return DataConnector
     */
    public function getDatabase() : DataConnector
    {
        return $this->dataConnector;
    }

    /**
     * @return DataSession[]
     */
    public function getDataSessions() : array
    {
        return $this->dataSessions;
    }

    /**
     * @param string $name
     * @return DataSession|null
     */
    public function getDataSession(string $name) : ?DataSession
    {
        return $this->dataSessions[strtolower($name)] ?? null;
    }

    /**
     * @param string $name
     */
    public function createDataSession(string $name) : void
    {
        $this->dataConnector->executeInsert("data.users.add", [
            "name" => $name
        ]);

        $this->dataSessions[strtolower($name)] = new DataSession($name, "a", 0, 0);
    }

    public function unload() : void
    {
        $this->dataConnector->close();
    }
}
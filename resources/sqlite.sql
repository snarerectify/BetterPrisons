-- #!sqlite

-- #{ table
    -- #{ users
CREATE TABLE IF NOT EXISTS PrisonUsers
(
    name           VARCHAR(32) PRIMARY KEY NOT NULL,
    rank           TEXT        DEFAULT "a",
    prestige       INTEGER     DEFAULT 0,
    blocksBroken   INTEGER     DEFAULT 0
);
-- #}
    -- #}

-- #{ data
    -- #{ users
        -- #{ add
            -- # :name string
            -- # :rank string "a"
            -- # :prestige integer 0
            -- # :blocksBroken integer 0
INSERT OR IGNORE INTO
PrisonUsers(name, rank, prestige, blocksBroken)
VALUES (:name, :rank, :prestige, :blocksBroken);
		-- #}
	    -- #{ get
			-- # :name string
SELECT * FROM PrisonUsers WHERE name = :name;
        -- #}
        -- #{ set
            -- # :name string
            -- # :rank string "a"
            -- # :prestige integer 0
            -- # :blocksBroken integer 0
INSERT OR REPLACE INTO
PrisonUsers(name, rank, prestige, blocksBroken)
VALUES (:name, :rank, :prestige, :blocksBroken);
		-- #}
		-- #{ getAll
SELECT * FROM PrisonUsers;
        -- #}
        -- #{ setRank
            -- # :name string
            -- # :rank string
INSERT INTO PrisonUsers(name, rank)
VALUES(:name, :rank)
ON CONFLICT(name) DO UPDATE SET rank = :rank;
        -- #}
        -- #{ setPrestige
            -- # :name string
            -- # :prestige integer
INSERT INTO PrisonUsers(name, prestige)
VALUES(:name, :prestige)
ON CONFLICT(name) DO UPDATE SET prestige = :prestige;
        -- #}
        -- #{setBlocksBroken
            -- # :name string
            -- # :blocksBroken
INSERT INTO PrisonUsers(name, prestige)
VALUES(:name, :prestige)
ON CONFLICT(name) DO UPDATE SET prestige = :prestige;
        -- #}
    -- #}
-- #}
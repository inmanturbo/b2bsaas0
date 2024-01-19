<?php

namespace B2bSaas;

class MariadbTeamDatabase extends TeamDatabase
{
    use HasParent;
    use ManagesMariadbDatabase;
}

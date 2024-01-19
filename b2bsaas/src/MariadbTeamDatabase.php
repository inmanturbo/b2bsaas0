<?php

namespace B2bSaas;

use B2bSaas\HasParent;
use B2bSaas\ManagesMariadbDatabase;

class MariadbTeamDatabase extends TeamDatabase
{
    use HasParent;
    use ManagesMariadbDatabase;
}

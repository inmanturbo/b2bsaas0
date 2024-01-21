<?php

namespace Inmanturbo\B2bSaas;

class MariadbTeamDatabase extends TeamDatabase
{
    use HasParent;
    use ManagesMariadbDatabase;
}

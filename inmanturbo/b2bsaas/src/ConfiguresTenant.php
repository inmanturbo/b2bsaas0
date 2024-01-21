<?php

namespace Inmanturbo\B2bSaas;

trait ConfiguresTenant
{
    use HasLandingPage;
    use HasTeamDatabase;

    public function configure()
    {
        config([
            'cache.prefix' => $this->id,
            'b2bsaas.company.logo_path' => $this->contact_data?->logoPath(),
            'b2bsaas.company.name' => $this->contact_data?->name(),
            'b2bsaas.company.phone' => $this->contact_data?->phone(),
            'b2bsaas.company.fax' => $this->contact_data?->fax(),
            'b2bsaas.company.street_address' => $this->contact_data?->streetAddress(),
            'b2bsaas.company.city_state_zip' => $this->contact_data?->cityStateZip(),
            'b2bsaas.company.email' => $this->contact_data?->email(),
        ]);

        // if not running unit tests
        if (! app()->runningUnitTests()) {
            $this->teamDatabase->configure();
        }

        app('cache')->purge(
            config('cache.default')
        );

        return $this;
    }

    public function use()
    {
        $this->teamDatabase->use();

        app()->forgetInstance('team');

        app()->instance('team', $this);

        app()->forgetInstance('contact');

        app()->instance('contact', $this->contact_data);

        return $this;
    }

    public function purge()
    {
        parent::purge();

        if (isset(app()['team']) && app()['team']->uuid === $this->uuid) {
            app()->forgetInstance('team');
            if (request()->user()->teams->count() > 0) {
                //switch to the first team
                request()->user()->switchTeam(request()->user()->teams?->first());
                request()->user()->teams?->first()?->use();
            }
        }
    }
}

<?php

namespace B2bSaas;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Browsershot\Browsershot;

trait HasLandingPage
{
    /**
     * Update the user's profile photo.
     *
     * @return void
     */
    public function updateLandingPage(UploadedFile $page)
    {
        tap($this->contact_data->landingPage(), function ($previous) use ($page) {
            $this->forceFill(['contact_data' => $value = array_merge($this->contact_data->toArray() ?? [], ['landingPage' => $page->storePublicly('landing-pages', ['disk' => $this->landingPageDisk()])]),
            ])->save();

            // $browserShot = Browsershot::html($page->get())->save(Storage::disk($this->landingPageDisk())
            // ->path($value['landingPage'].'.png'));

            if ($previous) {
                Storage::disk($this->landingPageDisk())->delete($previous);
            }
        });
    }

    /**
     * Delete the user's profile photo.
     *
     * @return void
     */
    public function deletelandingPage()
    {
        if (is_null($this->contact_data->landingPage())) {
            return;
        }

        Storage::disk($this->landingPageDisk())->delete($this->contact_data->landingPage());

        $this->forceFill(['contact_data' => json_encode(array_merge($this->contact_data->toArray(), [
            'landingPage' => null,
        ]))])->save();
    }

    /**
     * Get the URL to the user's profile photo.
     *
     * @return string
     */
    public function getLandingPageUrlAttribute()
    {
        // return '/v1/team-page/'. $this->slug;

        return $this->contact_data?->landingPage()
                    ? str_replace(url(''), '', '/v1/team-page/'.$this->slug)
                    : $this->defaultlandingPageUrl();

        return $this->contact_data?->landingPage()
                    ? str_replace(url(''), '', Storage::disk($this->landingPageDisk())->url($this->contact_data?->landingPage()))
                    : $this->defaultlandingPageUrl();
    }

    /**
     * Get the URL to the user's profile photo.
     *
     * @return string
     */
    public function getLandingPageThumbUrlAttribute()
    {
        return $this->contact_data->landingPage()
                    ? Storage::disk($this->landingPageDisk())->url($this->contact_data->landingPage().'.png')
                    : $this->defaultlandingPageUrl();
    }

    public function downloadLandingPage()
    {
        return Storage::disk($this->landingPageDisk())->download($this->contact_data->landingPage());
    }

    /**
     * Get the default profile photo URL if no profile photo has been uploaded.
     *
     * @return string
     */
    protected function defaultlandingPageUrl()
    {
        return url(config('b2bsaassss.company.default_landing_page_path', '/v1/team-page/'.$this->slug));
    }

    /**
     * Get the disk that profile photos should be stored on.
     *
     * @return string
     */
    public function landingPageDisk()
    {
        return isset($_ENV['VAPOR_ARTIFACT_NAME']) ? 's3' : config('b2bsaassss.company.landing_page_disk', 'public');
    }
}

<?php

namespace App\Observers\Nova;

use App\Link;
use App\Traits\Observers\Validator;

class LinkObserver
{
    use Validator;

    /**
     * Handle the link "saving" event.
     *
     * @param  \App\Link  $link
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Throwable
     */
    public function saving(Link $link)
    {
        $this->validate($link);
    }

    /**
     * The data array for validator.
     *
     * @return array
     */
    protected function data()
    {
        $linkType = $this->resource->linkable->links->every(function ($link) {
            return $link->linkType->isnot($this->resource->linkType);
        });

        return compact('linkType');
    }

    /**
     * The rules array for validator.
     *
     * @return array
     */
    protected function rules()
    {
        return ['linkType' => 'boolean|accepted'];
    }

    /**
     * The messages array for validator.
     *
     * @return array
     */
    protected function messages()
    {
        $message = __('validation.nova.linkType.accepted', [
            'type' => $this->resource->linktype->name,
        ]);

        return ['linkType.accepted' => $message];
    }
}

<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'The :attribute must be accepted.',
    'active_url' => 'The :attribute is not a valid URL.',
    'after' => 'The :attribute must be a date after :date.',
    'after_or_equal' => 'The :attribute must be a date after or equal to :date.',
    'alpha' => 'The :attribute may only contain letters.',
    'alpha_dash' => 'The :attribute may only contain letters, numbers, dashes and underscores.',
    'alpha_num' => 'The :attribute may only contain letters and numbers.',
    'array' => 'The :attribute must be an array.',
    'before' => 'The :attribute must be a date before :date.',
    'before_or_equal' => 'The :attribute must be a date before or equal to :date.',
    'between' => [
        'numeric' => 'The :attribute must be between :min and :max.',
        'file' => 'The :attribute must be between :min and :max kilobytes.',
        'string' => 'The :attribute must be between :min and :max characters.',
        'array' => 'The :attribute must have between :min and :max items.',
    ],
    'boolean' => 'The :attribute field must be true or false.',
    'confirmed' => 'The :attribute confirmation does not match.',
    'date' => 'The :attribute is not a valid date.',
    'date_equals' => 'The :attribute must be a date equal to :date.',
    'date_format' => 'The :attribute does not match the format :format.',
    'different' => 'The :attribute and :other must be different.',
    'digits' => 'The :attribute must be :digits digits.',
    'digits_between' => 'The :attribute must be between :min and :max digits.',
    'dimensions' => 'The :attribute has invalid image dimensions.',
    'distinct' => 'The :attribute field has a duplicate value.',
    'email' => 'The :attribute must be a valid email address.',
    'ends_with' => 'The :attribute must end with one of the following: :values.',
    'exists' => 'The selected :attribute is invalid.',
    'file' => 'The :attribute must be a file.',
    'filled' => 'The :attribute field must have a value.',
    'gt' => [
        'numeric' => 'The :attribute must be greater than :value.',
        'file' => 'The :attribute must be greater than :value kilobytes.',
        'string' => 'The :attribute must be greater than :value characters.',
        'array' => 'The :attribute must have more than :value items.',
    ],
    'gte' => [
        'numeric' => 'The :attribute must be greater than or equal :value.',
        'file' => 'The :attribute must be greater than or equal :value kilobytes.',
        'string' => 'The :attribute must be greater than or equal :value characters.',
        'array' => 'The :attribute must have :value items or more.',
    ],
    'image' => 'The :attribute must be an image.',
    'in' => 'The selected :attribute is invalid.',
    'in_array' => 'The :attribute field does not exist in :other.',
    'integer' => 'The :attribute must be an integer.',
    'ip' => 'The :attribute must be a valid IP address.',
    'ipv4' => 'The :attribute must be a valid IPv4 address.',
    'ipv6' => 'The :attribute must be a valid IPv6 address.',
    'json' => 'The :attribute must be a valid JSON string.',
    'lt' => [
        'numeric' => 'The :attribute must be less than :value.',
        'file' => 'The :attribute must be less than :value kilobytes.',
        'string' => 'The :attribute must be less than :value characters.',
        'array' => 'The :attribute must have less than :value items.',
    ],
    'lte' => [
        'numeric' => 'The :attribute must be less than or equal :value.',
        'file' => 'The :attribute must be less than or equal :value kilobytes.',
        'string' => 'The :attribute must be less than or equal :value characters.',
        'array' => 'The :attribute must not have more than :value items.',
    ],
    'max' => [
        'numeric' => 'The :attribute may not be greater than :max.',
        'file' => 'The :attribute may not be greater than :max kilobytes.',
        'string' => 'The :attribute may not be greater than :max characters.',
        'array' => 'The :attribute may not have more than :max items.',
    ],
    'mimes' => 'The :attribute must be a file of type: :values.',
    'mimetypes' => 'The :attribute must be a file of type: :values.',
    'min' => [
        'numeric' => 'The :attribute must be at least :min.',
        'file' => 'The :attribute must be at least :min kilobytes.',
        'string' => 'The :attribute must be at least :min characters.',
        'array' => 'The :attribute must have at least :min items.',
    ],
    'not_in' => 'The selected :attribute is invalid.',
    'not_regex' => 'The :attribute format is invalid.',
    'numeric' => 'The :attribute must be a number.',
    'password' => 'The password is incorrect.',
    'present' => 'The :attribute field must be present.',
    'regex' => 'The :attribute format is invalid.',
    'required' => 'The :attribute field is required.',
    'required_if' => 'The :attribute field is required when :other is :value.',
    'required_unless' => 'The :attribute field is required unless :other is in :values.',
    'required_with' => 'The :attribute field is required when :values is present.',
    'required_with_all' => 'The :attribute field is required when :values are present.',
    'required_without' => 'The :attribute field is required when :values is not present.',
    'required_without_all' => 'The :attribute field is required when none of :values are present.',
    'same' => 'The :attribute and :other must match.',
    'size' => [
        'numeric' => 'The :attribute must be :size.',
        'file' => 'The :attribute must be :size kilobytes.',
        'string' => 'The :attribute must be :size characters.',
        'array' => 'The :attribute must contain :size items.',
    ],
    'starts_with' => 'The :attribute must start with one of the following: :values.',
    'string' => 'The :attribute must be a string.',
    'timezone' => 'The :attribute must be a valid zone.',
    'unique' => 'The :attribute has already been taken.',
    'uploaded' => 'The :attribute failed to upload.',
    'url' => 'The :attribute format is invalid.',
    'uuid' => 'The :attribute must be a valid UUID.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        // Include in API
        'values' => 'values',
        'moments' => 'moments',
        'gender_id' => 'gender',
        'status_id' => 'status',
        'country_id' => 'country',
        'event_key' => 'event key',
        'language_id' => 'language',
        'education_id' => 'education',
        'mood_type_id' => 'mood type',
        'profession_id' => 'profession',
        'wod_id' => 'workouts of the day',

        'moments.*.event_key' => 'event key',
        'moments.*.values.*.key' => 'key',
        'moments.*.values.*.value' => 'value',
        'moments.*.values' => 'moment values',
        'moments.*.happened_at' => 'happened at',

        'values.*.key' => 'key',
        'values.*.value' => 'value',

        // include in API and Nova
        'ip' => 'ip',
        'key' => 'key',
        'email' => 'email',
        'level' => 'level',
        'point' => 'point',
        'version' => 'version',
        'dob' => 'date of birth',
        'ended_at' => 'ended at',
        'password' => 'password',
        'username' => 'username',
        'last_name' => 'lastname',
        'max_level' => 'max level',
        'first_name' => 'firstname',
        'slept_hour' => 'slept hour',
        'started_at' => 'started at',
        'device_name' => 'device name',
        'happened_at' => 'happened at',
        'device_id' => 'device identifier',
        'level_up_point' => 'level up point',
        'release_version' => 'release version',
        'allow_newsletter' => 'allow newsletter',

        // Include only in Nova
        'id' => 'id',
        'body' => 'body',
        'city' => 'city',
        'logo' => 'logo',
        'name' => 'name',
        'slug' => 'slug',
        'field' => 'field',
        'image' => 'image',
        'title' => 'title',
        'state' => 'state',
        'value' => 'value',
        'score' => 'score',
        'locale' => 'locale',
        'scopes' => 'scopes',
        'secret' => 'secret',
        'teaser' => 'teaser',
        'address' => 'address',
        'current' => 'current',
        'default' => 'default',
        'excerpt' => 'excerpt',
        'feature' => 'feature',
        'primary' => 'primary',
        'revoked' => 'revoked',
        'guard_name' => 'guard',
        'approved' => 'approved',
        'iso_code' => 'iso code',
        'redirect' => 'redirect',
        'direction' => 'direction',
        'two_hands' => 'two hands',
        'telephone' => 'telephone',
        'expires_at' => 'expires at',
        'headphones' => 'headphones',
        'postal_code' => 'postal code',
        'force_update' => 'force update',
        'numeric_code' => 'numeric code',
        'around_player' => 'around player',
        'effectiveness' => 'effectiveness',
        'is_superadmin' => 'is superadmin',
        'language_code' => 'language code',
        'is_organization' => 'is organization',
        'password_client' => 'password client',
        'protect_privacy' => 'protect privacy',
        'color_perception' => 'color perception',
        'email_verified_at' => 'email verified at',
        'access_admin_panel' => 'access admin panel',
        'high_movement_intensity' => 'high movement intensity',

        // Include in Nova Relations
        'game' => 'game',
        'user' => 'user',
        'event' => 'event',
        'client' => 'client',
        'gender' => 'gender',
        'moment' => 'moment',
        'parent' => 'parent',
        'status' => 'status',
        'country' => 'country',
        'session' => 'session',
        'language' => 'language',
        'platform' => 'platform',
        'scorable' => 'scorable',
        'versions' => 'versions',
        'gameType' => 'game type',
        'moodType' => 'mood type',
        'education' => 'education',
        'profession' => 'profession',
        'localizable' => 'localizable',
        'wod' => 'workouts of the day',
        'organization' => 'organization',
        'activityType' => 'activity type',
    ],

];

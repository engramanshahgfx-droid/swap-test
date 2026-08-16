<?php

return [
    'required' => 'حقل :attribute مطلوب.',
    'email' => 'يجب أن يكون :attribute عنوان بريد إلكتروني صحيح.',
    'min' => [
        'numeric' => 'يجب أن يكون :attribute على الأقل :min.',
        'file' => 'يجب أن يكون حجم :attribute على الأقل :min كيلوبايت.',
        'string' => 'يجب أن يكون :attribute على الأقل :min أحرف.',
        'array' => 'يجب أن يحتوي :attribute على الأقل على :min عناصر.',
    ],
    'max' => [
        'numeric' => 'يجب ألا يكون :attribute أكبر من :max.',
        'file' => 'يجب ألا يتجاوز حجم :attribute :max كيلوبايت.',
        'string' => 'يجب ألا يتجاوز طول :attribute :max أحرف.',
        'array' => 'يجب ألا يحتوي :attribute على أكثر من :max عناصر.',
    ],
    'size' => [
        'numeric' => 'يجب أن يكون :attribute بقيمة :size.',
        'file' => 'يجب أن يكون حجم :attribute :size كيلوبايت.',
        'string' => 'يجب أن يتكون :attribute من :size أحرف.',
        'array' => 'يجب أن يحتوي :attribute على :size عناصر.',
    ],
    'between' => [
        'numeric' => 'يجب أن يكون :attribute بين :min و :max.',
        'file' => 'يجب أن يكون حجم :attribute بين :min و :max كيلوبايت.',
        'string' => 'يجب أن يكون طول :attribute بين :min و :max أحرف.',
        'array' => 'يجب أن يحتوي :attribute على عدد عناصر بين :min و :max.',
    ],
    'confirmed' => 'تأكيد :attribute غير متطابق.',
    'unique' => ':attribute قد تم استخدامه بالفعل.',
    'exists' => ':attribute المحدد غير صحيح.',
    'regex' => 'صيغة :attribute غير صحيحة.',
    'numeric' => ':attribute يجب أن يكون رقم.',
    'integer' => ':attribute يجب أن يكون عدد صحيح.',
    'string' => ':attribute يجب أن يكون نص.',
    'date' => ':attribute ليس تاريخ صحيح.',
    'json' => ':attribute يجب أن يكون JSON صحيح.',
];

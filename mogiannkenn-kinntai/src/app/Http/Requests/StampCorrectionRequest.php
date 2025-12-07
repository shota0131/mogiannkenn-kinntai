<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StampCorrectionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'start_time'   => 'required|date_format:H:i',
            'end_time'     => 'required|date_format:H:i|after:start_time',

            'break_start'  => 'nullable|date_format:H:i|after_or_equal:start_time|before_or_equal:end_time',
            'break_end'    => 'nullable|date_format:H:i|after_or_equal:break_start|before_or_equal:end_time',

            'remarks'      => 'required|string|max:500',
        ];
    }

    public function messages()
    {
        return [
            'end_time.after' => '出勤時間もしくは退勤時間が不適切な値です',

            'break_start.after_or_equal' => '休憩時間が不適切な値です',
            'break_start.before_or_equal' => '休憩時間が不適切な値です',

            'break_end.after_or_equal' => '休憩時間もしくは退勤時間が不適切な値です',
            'break_end.before_or_equal' => '休憩時間もしくは退勤時間が不適切な値です',

            'remarks.required' => '備考を記入してください',
        ];
    }
}


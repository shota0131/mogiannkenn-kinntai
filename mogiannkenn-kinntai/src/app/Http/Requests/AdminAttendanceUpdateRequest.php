<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminAttendanceUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'start_time'  => $this->start_time ?: null,
            'end_time'    => $this->end_time ?: null,
            'break_start' => $this->break_start ?: null,
            'break_end'   => $this->break_end ?: null,
        ]);
    }

    public function rules()
    {
        return [
            'start_time'  => ['nullable', 'regex:/^\d{2}:\d{2}$/'],
            'end_time'    => ['nullable', 'regex:/^\d{2}:\d{2}$/', 'after:start_time'],
            'break_start' => ['nullable', 'regex:/^\d{2}:\d{2}$/', 'after_or_equal:start_time', 'before_or_equal:end_time'],
            'break_end'   => ['nullable', 'regex:/^\d{2}:\d{2}$/', 'after_or_equal:break_start', 'before_or_equal:end_time'],
            'remarks'     => 'required|string|max:500',
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




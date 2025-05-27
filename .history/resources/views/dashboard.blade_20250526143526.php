@extends('layouts.app')

@section('content')
    <div class="flex flex-col md:flex-row gap-4 mb-6">
    <div class="w-full md:w-1/4">
        <label class="block text-sm text-gray-700 mb-1">ปีเริ่มต้น</label>
        <select id="startYear" class="w-full border rounded p-2">
            <option value="">ทั้งหมด</option>
            @for ($year = now()->year + 543; $year >= 2557; $year--)
                <option value="{{ $year }}">{{ $year }}</option>
            @endfor
        </select>
    </div>

    <div class="w-full md:w-1/4">
        <label class="block text-sm text-gray-700 mb-1">ปีสิ้นสุด</label>
        <select id="endYear" class="w-full border rounded p-2">
            <option value="">ทั้งหมด</option>
            @for ($year = now()->year + 543; $year >= 2557; $year--)
                <option value="{{ $year }}">{{ $year }}</option>
            @endfor
        </select>
    </div>

    <div class="w-full md:w-1/4">
        <label class="block text-sm text-gray-700 mb-1">ค้นหาชื่อทุน</label>
        <input type="text" id="fundingSearch" class="w-full border rounded p-2" placeholder="ชื่อทุน...">
    </div>

    <div class="w-full md:w-1/4">
        <label class="block text-sm text-gray-700 mb-1">ค้นหาชื่อโครงการ</label>
        <input type="text" id="projectSearch" class="w-full border rounded p-2" placeholder="ชื่อโครงการ...">
    </div>
</div>

<canvas id="fundingChart" height="120"></canvas>

<div class="mt-6">
    <h3 class="font-semibold text-gray-700 mb-2">📋 รายชื่อโครงการที่พบ</h3>
    <ul id="projectList" class="list-disc pl-5 text-sm text-gray-800"></ul>
</div>

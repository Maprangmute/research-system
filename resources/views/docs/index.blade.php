<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ศูนย์เอกสาร</title>
  <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Thai+Looped:wght@400;500;600&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body { font-family: 'IBM Plex Sans Thai Looped', sans-serif; }
  </style>
</head>

<body class="bg-white text-slate-800">

  <!-- Header -->
  <header class="border-b border-slate-200 bg-white sticky top-0 z-10">
    <div class="max-w-5xl mx-auto flex justify-between items-center px-4 py-4">
      <h1 class="text-xl md:text-2xl font-bold text-sky-700">📁 ศูนย์เอกสารโครงการ</h1>
      <form method="POST" action="{{ route('docs.logout') }}">
        @csrf
        <button type="submit"
          class="text-sm px-3 py-1.5 rounded-md border border-sky-200 bg-sky-50 hover:bg-sky-100 text-sky-700 font-medium transition">
          ออกจากระบบ
        </button>
      </form>
    </div>
  </header>

<main class="max-w-5xl mx-auto px-4 py-10">
<img src="{{ asset('images/BANNER/Button_FALL07-01.png') }}" class="w-64 mx-auto">



  <img src="{{ asset('images/BANNER/Button_FALL07-02.png') }}" 
  class="w-64  ">
  <div class="bg-white  border-slate-200 rounded-xl shadow-sm p-2">
    <ul class="text-base leading-relaxed divide-y divide-slate-900">
      <!-- 🔹 หมวด A -->
      <li class="py-2"><a href="{{ asset('docs/A001.pdf') }}" class="text-sky-700 hover:text-sky-900 hover:underline transition">A001 คู่มือการดำเนินงานของ รพ.สต. สำหรับ กลุ่มมาตรการ (Intervention group)</a></li>
      <li class="py-2"><a href="{{ asset('docs/A002.pdf') }}" class="text-sky-700 hover:text-sky-900 hover:underline transition">A002 คู่มือการดำเนินงานของ รพ.สต. สำหรับ กลุ่มควบคุม (Control group)</a></li>
      <li class="py-2"><a href="{{ asset('docs/A003.pdf') }}" class="text-sky-700 hover:text-sky-900 hover:underline transition">A003 LINK APPLICATION : หน่วยบริการ</a></li>
      <li class="py-2"><a href="{{ asset('docs/A004.pdf') }}" class="text-sky-700 hover:text-sky-900 hover:underline transition">A004 คู่มือการใช้ APPLICATION : หน่วยบริการ</a></li>
    </ui>
    <div>
        <br>
    <div>
    <ul class="text-base leading-relaxed divide-y divide-slate-900">
      <li class="py-2"><a href="{{ asset('docs/A005.pdf') }}" class="text-sky-700 hover:text-sky-900 hover:underline transition">A005 แบบประเมินความสามารถด้านการทรงตัวและการเดิน (TUG) - ครั้งที่ 1 การประเมินก่อนเริ่มมาตรการ</a></li>
      <li class="py-2"><a href="{{ asset('docs/A006.pdf') }}" class="text-sky-700 hover:text-sky-900 hover:underline transition">A006 แบบประเมินความสามารถด้านการทรงตัวและการเดิน (TUG) - ครั้งที่ 2 การประเมินเดือนที่ 3</a></li>
      <li class="py-2"><a href="{{ asset('docs/A007.pdf') }}" class="text-sky-700 hover:text-sky-900 hover:underline transition">A007 แบบประเมินความสามารถด้านการทรงตัวและการเดิน (TUG) - ครั้งที่ 3 การประเมินเดือนที่ 6</a></li>
      <li class="py-2"><a href="{{ asset('docs/A008.pdf') }}" class="text-sky-700 hover:text-sky-900 hover:underline transition">A008 แบบประเมินความสามารถด้านการทรงตัวและการเดิน (TUG) - ครั้งที่ 4 การประเมินผลลัพธ์หลังสิ้นสุดมาตรการเดือนที่ 12</a></li>
      <li class="py-2"><a href="{{ asset('docs/A009.pdf') }}" class="text-sky-700 hover:text-sky-900 hover:underline transition">A009 แบบประเมินการทรงตัว (Balance test) - ครั้งที่ 1 การประเมินก่อนเริ่มมาตรการ</a></li>
      <li class="py-2"><a href="{{ asset('docs/A010.pdf') }}" class="text-sky-700 hover:text-sky-900 hover:underline transition">A010 แบบประเมินการทรงตัว (Balance test) - ครั้งที่ 2 การประเมินผลลัพธ์หลังสิ้นสุดมาตรการเดือนที่ 12</a></li>
      <li class="py-2"><a href="{{ asset('docs/A011.pdf') }}" class="text-sky-700 hover:text-sky-900 hover:underline transition">A011 แบบประเมิน STEADI checklist 3 คําถาม - ครั้งที่ 1 การประเมินก่อนเริ่มมาตรการ</a></li>
      <li class="py-2"><a href="{{ asset('docs/A012.pdf') }}" class="text-sky-700 hover:text-sky-900 hover:underline transition">A012 แบบประเมิน STEADI checklist 3 คําถาม - ครั้งที่ 2 การประเมินผลลัพธ์หลังสิ้นสุดมาตรการเดือนที่ 12</a></li>
      <li class="py-2"><a href="{{ asset('docs/A013.pdf') }}" class="text-sky-700 hover:text-sky-900 hover:underline transition">A013 แบบประเมินการมองเห็น (Visual Acuity) - ครั้งที่ 1 การประเมินก่อนเริ่มมาตรการ</a></li>
      <li class="py-2"><a href="{{ asset('docs/A014.pdf') }}" class="text-sky-700 hover:text-sky-900 hover:underline transition">A014 แบบประเมินการมองเห็น (Visual Acuity) - ครั้งที่ 2 การประเมินผลลัพธ์หลังสิ้นสุดมาตรการเดือนที่ 12</a></li>
      <li class="py-2"><a href="{{ asset('docs/A015.pdf') }}" class="text-sky-700 hover:text-sky-900 hover:underline transition">A015 แบบประเมินภาวะเปราะบางอย่างง่าย (Frail scale) - ครั้งที่ 1 การประเมินก่อนเริ่มมาตรการ</a></li>
      <li class="py-2"><a href="{{ asset('docs/A016.pdf') }}" class="text-sky-700 hover:text-sky-900 hover:underline transition">A016 แบบประเมินภาวะเปราะบางอย่างง่าย (Frail scale) - ครั้งที่ 2 การประเมินผลลัพธ์หลังสิ้นสุดมาตรการเดือนที่ 12</a></li>
      <li class="py-2"><a href="{{ asset('docs/A017.pdf') }}" class="text-sky-700 hover:text-sky-900 hover:underline transition">A017 แบบประเมินความเสี่ยงต่อการพลัดตกหกล้มในผู้สูงอายุ (Thai FRAT) - ครั้งที่ 1 การประเมินก่อนเริ่มมาตรการ</a></li>
      <li class="py-2"><a href="{{ asset('docs/A018.pdf') }}" class="text-sky-700 hover:text-sky-900 hover:underline transition">A018 แบบประเมินความเสี่ยงต่อการพลัดตกหกล้มในผู้สูงอายุ (Thai FRAT) - ครั้งที่ 2 การประเมินผลลัพธ์หลังสิ้นสุดมาตรการเดือนที่ 12</a></li>
      <li class="py-2"><a href="{{ asset('docs/A019.pdf') }}" class="text-sky-700 hover:text-sky-900 hover:underline transition">A019 แบบประเมินลุก-นั่ง 5 ครั้ง (Five sit to stand) - ครั้งที่ 1 การประเมินก่อนเริ่มมาตรการ</a></li>
      <li class="py-2"><a href="{{ asset('docs/A020.pdf') }}" class="text-sky-700 hover:text-sky-900 hover:underline transition">A020 แบบประเมินลุก-นั่ง 5 ครั้ง (Five sit to stand) - ครั้งที่ 2 การประเมินผลลัพธ์หลังสิ้นสุดมาตรการเดือนที่ 12</a></li>
      <li class="py-2"><a href="{{ asset('docs/A021.pdf') }}" class="text-sky-700 hover:text-sky-900 hover:underline transition">A021 แบบประเมินคุณภาพชีวิต EQ-5D-5L - ครั้งที่ 1 การประเมินก่อนเริ่มมาตรการ</a></li>
      <li class="py-2"><a href="{{ asset('docs/A022.pdf') }}" class="text-sky-700 hover:text-sky-900 hover:underline transition">A022 แบบประเมินคุณภาพชีวิต EQ-5D-5L - ครั้งที่ 2 การประเมินเดือนที่ 3</a></li>
      <li class="py-2"><a href="{{ asset('docs/A023.pdf') }}" class="text-sky-700 hover:text-sky-900 hover:underline transition">A023 แบบประเมินคุณภาพชีวิต EQ-5D-5L - ครั้งที่ 3 การประเมินเดือนที่ 6</a></li>
      <li class="py-2"><a href="{{ asset('docs/A024.pdf') }}" class="text-sky-700 hover:text-sky-900 hover:underline transition">A024 แบบประเมินคุณภาพชีวิต EQ-5D-5L - ครั้งที่ 4 การประเมินผลลัพธ์หลังสิ้นสุดมาตรการเดือนที่ 12</a></li>
      <li class="py-2"><a href="{{ asset('docs/A025.pdf') }}" class="text-sky-700 hover:text-sky-900 hover:underline transition">A025 แบบทดสอบแรงบีบมือ (Grip Strength) - ครั้งที่ 1 การประเมินก่อนเริ่มมาตรการ</a></li>
      <li class="py-2"><a href="{{ asset('docs/A026.pdf') }}" class="text-sky-700 hover:text-sky-900 hover:underline transition">A026 แบบทดสอบแรงบีบมือ (Grip Strength) - ครั้งที่ 2 การประเมินเดือนที่ 3</a></li>
      <li class="py-2"><a href="{{ asset('docs/A027.pdf') }}" class="text-sky-700 hover:text-sky-900 hover:underline transition">A027 แบบทดสอบแรงบีบมือ (Grip Strength) - ครั้งที่ 3 การประเมินเดือนที่ 6</a></li>
       </ui>
    <div>
        <br>
    <div>
    <ul class="text-base leading-relaxed divide-y divide-slate-900">
      <li class="py-2"><a href="{{ asset('docs/A028.pdf') }}" class="text-sky-700 hover:text-sky-900 hover:underline transition">A028 แบบทดสอบแรงบีบมือ (Grip Strength) - ครั้งที่ 4 การประเมินผลลัพธ์หลังสิ้นสุดมาตรการเดือนที่ 12</a></li>
       </ui>
    <div>
        <br>
    <div>
    <ul class="text-base leading-relaxed divide-y divide-slate-900">
      <li class="py-2"><a href="{{ asset('docs/A029.pdf') }}" class="text-sky-700 hover:text-sky-900 hover:underline transition">A029 PDPA : เอกสารขอความยินยอม และรายละเอียดโครงการ</a></li>
       </ui>
    <div>
        <br>
    <div>
    </div>
    

  <img src="{{ asset('images/BANNER/Button_FALL07-03.png') }}" 
  class="w-64 ">


    <ul class="text-base leading-relaxed divide-y divide-slate-900">
      <!-- หมวด B -->
      <li class="py-2"><a href="{{ asset('docs/B001.pdf') }}" class="text-sky-700 hover:text-sky-900 hover:underline transition">B001 คู่มือเดินเยี่ยม- AMGEN</a></li>
      <li class="py-2"><a href="{{ asset('docs/B002.pdf') }}" class="text-sky-700 hover:text-sky-900 hover:underline transition">B002 คู่มือการดำเนินงานเดินเยี่ยมของอสม. กลุ่มมาตรการ (Intervention group)</a></li>
      <li class="py-2"><a href="{{ asset('docs/B003.pdf') }}" class="text-sky-700 hover:text-sky-900 hover:underline transition">B003 คู่มือการดำเนินงานเดินเยี่ยมของอสม. กลุ่มควบคุม (Control group)</a></li>
      <li class="py-2"><a href="{{ asset('docs/B004.pdf') }}" class="text-sky-700 hover:text-sky-900 hover:underline transition">B004 LINK APPLICATION : อสม/อสส</a></li>
      <li class="py-2"><a href="{{ asset('docs/B005.pdf') }}" class="text-sky-700 hover:text-sky-900 hover:underline transition">B005 คู่มือการใช้ APPLICATION : อสม/อสส</a></li>
      <li class="py-2"><a href="{{ asset('docs/B006.pdf') }}" class="text-sky-700 hover:text-sky-900 hover:underline transition">B006 แบบบันทึกการติดตามการเยี่ยมบ้านผู้ป่วยกระดูกสะโพกหัก (ครั้งที่ 1 เยี่ยมบ้านภายใน 30 วันหลังจำหน่าย)</a></li>
        </ui>
    <div>
         <img src="{{ asset('images/BANNER/Button_FALL07-04.png') }}" class="w-64 ">
    <div>
    <ul class="text-base leading-relaxed divide-y divide-slate-900">
      <li class="py-2"><a href="{{ asset('docs/B007.pdf') }}" class="text-sky-700 hover:text-sky-900 hover:underline transition">B007 แบบบันทึกการติดตามการเยี่ยมบ้านผู้ป่วยกระดูกสะโพกหัก (ครั้งที่ 2 เยี่ยมบ้านภายใน 3 เดือนหลังจำหน่าย)</a></li>
      <li class="py-2"><a href="{{ asset('docs/B008.pdf') }}" class="text-sky-700 hover:text-sky-900 hover:underline transition">B008 แบบบันทึกการติดตามการเยี่ยมบ้านผู้ป่วยกระดูกสะโพกหัก (ครั้งที่ 3 เยี่ยมบ้านภายใน 1 ปี หลังจำหน่าย)</a></li>
      <li class="py-2"><a href="{{ asset('docs/B009.pdf') }}" class="text-sky-700 hover:text-sky-900 hover:underline transition">B009 การประเมิน ADL - มีแต่คะแนน แต่ไม่เห็นแบบบันทึก</a></li>
      <li class="py-2"><a href="{{ asset('docs/B010.pdf') }}" class="text-sky-700 hover:text-sky-900 hover:underline transition">B010 แบบประเมินปัจจัยเสี่ยงต่อการพลัดตกหกล้มด้านสิ่งแวดล้อม</a></li>
      <li class="py-2"><a href="{{ asset('docs/B011.pdf') }}" class="text-sky-700 hover:text-sky-900 hover:underline transition">B011 แบบคัดกรองเบื้องต้น 9 คำถาม</a></li>
    </ui>
    <div>
        <img src="{{ asset('images/BANNER/Button_FALL07-06.png') }}" class="w-64 ">

    <div>
    <ul class="text-base leading-relaxed divide-y divide-slate-900">
      <li class="py-2"><a href="{{ asset('docs/C001.pdf') }}" class="text-sky-700 hover:text-sky-900 hover:underline transition">C001 คู่มือการดำเนินงานการดูแลผู้ที่มีความเสี่ยงต่อการพลัดตกหกล้ม: คลินิกผู้สูงอายุในโรงพยาบาล</a></li>
       </ui>
    <div>
        <img src="{{ asset('images/BANNER/Button_FALL07-05.png') }}" class="w-64 ">

    <div>
    <ul class="text-base leading-relaxed divide-y divide-slate-900">
      <li class="py-2"><a href="{{ asset('docs/D001.pdf') }}" class="text-sky-700 hover:text-sky-900 hover:underline transition">D001 คู่มือการดำเนินงานการดูแลผู้ป่วยสูงอายุที่กระดูกสะโพกหัก: โรงพยาบาล</a></li>
       </ui>
    <div>
         <br>
    <div>
    <ul class="text-base leading-relaxed divide-y divide-slate-900">
      <li class="py-2"><a href="{{ asset('docs/E001.pdf') }}" class="text-sky-700 hover:text-sky-900 hover:underline transition">E001 คู่มือเดินดี (ไดอารี่ เดินดี) -AMGEN</a></li>
      <li class="py-2"><a href="{{ asset('docs/E002.pdf') }}" class="text-sky-700 hover:text-sky-900 hover:underline transition">E002 LINK APPLICATION : ผู้สูงอายุ (MockUp MarveL)</a></li>

    </ul>
  </div>
</main>


  <footer class="border-t border-slate-200 bg-white text-center text-sm text-slate-500 py-6">
    © {{ date('Y') }} ศูนย์เอกสารโครงการ “เดินดี” — พัฒนาโดย communication team SiHP
  </footer>

</body>
</html>

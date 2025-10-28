<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class DocumentController extends Controller
{
    private $password = 'sihp2025'; // 🔑 รหัสก่อนเข้า

    public function index(Request $request)
    {
        if (!Session::get('authorized')) {
            return view('docs.login');
        }

        // 🔽 รายการเอกสาร (เก็บใน public/docs)
        $files = [
            ['name' => 'A001 คู่มือการดำเนินงานของ รพ.สต. สำหรับ กลุ่มมาตรการ (Intervention group)', 'url' => asset('docs/A001.pdf')],
            ['name' => 'A002 คู่มือการดำเนินงานของ รพ.สต. สำหรับ กลุ่มควบคุม (Control group)', 'url' => asset('docs/A002.pdf')],
            ['name' => 'A003 LINK APPLICATION : หน่วยบริการ', 'url' => asset('docs/A003.pdf')],
            ['name' => 'A004 คู่มือการใช้ APPLICATION : หน่วยบริการ', 'url' => asset('docs/A004.pdf')],
            
            ['name' => 'A005 แบบประเมินความสามารถด้านการทรงตัวและการเดิน (TUG)- ครั้งที่ 1 การประเมินก่อนเริ่มมาตรการ', 'url' => asset('docs/A005.pdf')],
            ['name' => 'A006 แบบประเมินความสามารถด้านการทรงตัวและการเดิน (TUG)- ครั้งที่ 2 การประเมินเดือนที่ 3', 'url' => asset('docs/A006.pdf')],
            ['name' => 'A007 แบบประเมินความสามารถด้านการทรงตัวและการเดิน (TUG)- ครั้งที่ 3 การประเมินเดือนที่ 6', 'url' => asset('docs/A007.pdf')],
            ['name' => 'A008 แบบประเมินความสามารถด้านการทรงตัวและการเดิน (TUG)- ครั้งที่ 4 การประเมินผลลัพธ์หลังสิ้นสุดมาตรการเดือนที่ 12', 'url' => asset('docs/A008.pdf')],
            ['name' => 'A009 แบบประเมินการทรงตัว (Balance test) - ครั้งที่ 1 การประเมินก่อนเริ่มมาตรการ', 'url' => asset('docs/A009.pdf')],
            ['name' => 'A010 แบบประเมินการทรงตัว (Balance test) - ครั้งที่ 2 การประเมินผลลัพธ์หลังสิ้นสุดมาตรการเดือนที่ 12', 'url' => asset('docs/A010.pdf')],
            ['name' => 'A011 แบบประเมิน STEADI checklist 3 คําถาม - ครั้งที่ 1 การประเมินก่อนเริ่มมาตรการ', 'url' => asset('docs/A011.pdf')],
            ['name' => 'A012 แบบประเมิน STEADI checklist 3 คําถาม - ครั้งที่ 2 การประเมินผลลัพธ์หลังสิ้นสุดมาตรการเดือนที่ 12', 'url' => asset('docs/A012.pdf')],
            ['name' => 'A013 แบบประเมินการมองเห็น (Visual Acuity) - ครั้งที่ 1 การประเมินก่อนเริ่มมาตรการ', 'url' => asset('docs/A013.pdf')],
            ['name' => 'A014 แบบประเมินการมองเห็น (Visual Acuity) - ครั้งที่ 2 การประเมินผลลัพธ์หลังสิ้นสุดมาตรการเดือนที่ 12', 'url' => asset('docs/A014.pdf')],
            ['name' => 'A015 แบบประเมินภาวะเปราะบางอย่างง่าย (Frail scale) - ครั้งที่ 1 การประเมินก่อนเริ่มมาตรการ', 'url' => asset('docs/A015.pdf')],
            ['name' => 'A016 แบบประเมินภาวะเปราะบางอย่างง่าย (Frail scale) - ครั้งที่ 2 การประเมินผลลัพธ์หลังสิ้นสุดมาตรการเดือนที่ 12', 'url' => asset('docs/A016.pdf')],
            ['name' => 'A017 แบบประเมินความเสี่ยงต่อการพลัดตกหกล้มในผู้สูงอายุ (Thai FRAT) - ครั้งที่ 1 การประเมินก่อนเริ่มมาตรการ', 'url' => asset('docs/A017.pdf')],
            ['name' => 'A018 แบบประเมินความเสี่ยงต่อการพลัดตกหกล้มในผู้สูงอายุ (Thai FRAT) - ครั้งที่ 2 การประเมินผลลัพธ์หลังสิ้นสุดมาตรการเดือนที่ 12', 'url' => asset('docs/A018.pdf')],
            ['name' => 'A019 แบบประเมินลุก-นั่ง 5 ครั้ง (Five sit to stand) - ครั้งที่ 1 การประเมินก่อนเริ่มมาตรการ', 'url' => asset('docs/A019.pdf')],
            ['name' => 'A020 แบบประเมินลุก-นั่ง 5 ครั้ง (Five sit to stand) - ครั้งที่ 2 การประเมินผลลัพธ์หลังสิ้นสุดมาตรการเดือนที่ 12', 'url' => asset('docs/A020.pdf')],
            ['name' => 'A021 แบบประเมินคุณภาพชีวิต EQ-5D-5L - ครั้งที่ 1 การประเมินก่อนเริ่มมาตรการ', 'url' => asset('docs/A021.pdf')],
            ['name' => 'A022 แบบประเมินคุณภาพชีวิต EQ-5D-5L - ครั้งที่ 2 การประเมินเดือนที่ 3', 'url' => asset('docs/A022.pdf')],
            ['name' => 'A023 แบบประเมินคุณภาพชีวิต EQ-5D-5L - ครั้งที่ 3 การประเมินเดือนที่ 6', 'url' => asset('docs/A023.pdf')],
            ['name' => 'A024 แบบประเมินคุณภาพชีวิต EQ-5D-5L - ครั้งที่ 4 การประเมินผลลัพธ์หลังสิ้นสุดมาตรการเดือนที่ 12', 'url' => asset('docs/A024.pdf')],
            ['name' => 'A025 แบบทดสอบแรงบีบมือ (Grip Strength) - ครั้งที่ 1 การประเมินก่อนเริ่มมาตรการ', 'url' => asset('docs/A025.pdf')],
            ['name' => 'A026 แบบทดสอบแรงบีบมือ (Grip Strength) - ครั้งที่ 2 การประเมินเดือนที่ 3', 'url' => asset('docs/A026.pdf')],
            ['name' => 'A027 แบบทดสอบแรงบีบมือ (Grip Strength) - ครั้งที่ 3 การประเมินเดือนที่ 6', 'url' => asset('docs/A027.pdf')],
            ['name' => 'A028 แบบทดสอบแรงบีบมือ (Grip Strength) - ครั้งที่ 4 การประเมินผลลัพธ์หลังสิ้นสุดมาตรการเดือนที่ 12', 'url' => asset('docs/A028.pdf')],
            
            ['name' => 'A029 PDPA : เอกสารขอความยินยอม และรายละเอียดโครงการ', 'url' => asset('docs/A029.pdf')],
            ['name' => 'B001 คู่มือเดินเยี่ยม- AMGEN', 'url' => asset('docs/B001.pdf')],
            ['name' => 'B002 คู่มือการดำเนินงานเดินเยี่ยมของอสม. กลุ่มมาตรการ (Intervention group)', 'url' => asset('docs/B002.pdf')],
            ['name' => 'B003 คู่มือการดำเนินงานเดินเยี่ยมของอสม. กลุ่มควบคุม (Control group)', 'url' => asset('docs/B003.pdf')],
            ['name' => 'B004 LINK APPLICATION : อสม/อสส', 'url' => asset('docs/B004.pdf')],
            ['name' => 'B005 คู่มือการใช้ APPLICATION : อสม/อสส', 'url' => asset('docs/B005.pdf')],
            
            ['name' => 'B006 แบบบันทึกการติดตามการเยี่ยมบ้านผู้ป่วยกระดูกสะโพกหัก ( ครั้งที่ 1 เยี่ยมบ้านภายใน 30 วันหลังจำหน่าย)', 'url' => asset('docs/B006.pdf')],
            ['name' => 'B007 แบบบันทึกการติดตามการเยี่ยมบ้านผู้ป่วยกระดูกสะโพกหัก (ครั้งที่ 2 เยี่ยมบ้านภายใน 3 เดือนหลังจำหน่าย)', 'url' => asset('docs/B007.pdf')],
            ['name' => 'B008 แบบบันทึกการติดตามการเยี่ยมบ้านผู้ป่วยกระดูกสะโพกหัก (ครั้งที่ 3 เยี่ยมบ้านภายใน 1 ปี หลังจำหน่าย)', 'url' => asset('docs/B008.pdf')],
            ['name' => 'B009 การประเมิน ADL - มีแต่คะแนน แต่ไม่เห็นแบบบันทึก', 'url' => asset('docs/B009.pdf')],
            ['name' => 'B010 แบบประเมินปัจจัยเสี่ยงต่อการพลัดตกหกล้มด้านสิ่งแวดล้อม', 'url' => asset('docs/B010.pdf')],
            ['name' => 'B011 แบบคัดกรองเบื้องต้น 9 คำถาม', 'url' => asset('docs/B011.pdf')],
            ['name' => 'C001 คู่มือการดำเนินงานการดูแลผู้ที่มีความเสี่ยงต่อการพลัดตกหกล้ม: คลินิกผู้สูงอายุในโรงพยาบาล', 'url' => asset('docs/C001.pdf')],
            ['name' => 'D001 คู่มือการดำเนินงานการดูแลผู้ป่วยสูงอายุที่กระดูกสะโพกหัก: โรงพยาบาล', 'url' => asset('docs/D001.pdf')],

            ['name' => 'E001 คู่มือเดินดี (ไดอารี่ เดินดี) -AMGEN', 'url' => asset('docs/E001.pdf')],
            ['name' => 'E002 LINK APPLICATION : ผู้สูงอายุ (MockUp MarveL)', 'url' => asset('docs/E002.pdf')],
        ];

        return view('docs.index', compact('files'));
    }

   public function login(Request $request)
    {
        if ($request->password === $this->password) {
            Session::put('authorized', true);
            return redirect()->route('docs.index');
        }
        return back()->with('error', 'รหัสผ่านไม่ถูกต้อง');
    }

    public function logout()
    {
        Session::forget('authorized'); // ❌ เคลียร์ session ออกจากระบบ
        return redirect()->route('docs.index'); // กลับไปหน้า login
    }
}

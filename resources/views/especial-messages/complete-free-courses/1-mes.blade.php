Hola!!
 {{$student['NOMBRE']}}
 
 @php
    $text = '';
    $s = count($student['courses']) > 1 ? 's' : '';
    $one_day = [];
    $four_days = [];
    $seven_days = [];
    $fifteen_days = [];
    $one_month = [];
    $excel = [];
    $mbi_and_msp_siF = 0;

    foreach ($student['courses'] as $key => $course) {
        switch ($course['diff_days']) {
            case 1:
                $one_day[] = $course;
                break;
            case 4:
                $four_days[] = $course;
                break;
            case 7:
                $seven_days[] = $course;
                break;
            case 15:
                $fifteen_days[] = $course;
                break;
            default:{
                if($course['diff_days'] == $course['month_days'])
                    $one_month[] = $course;
                break;
            }
        }
    }

    foreach ($one_month as $key => $course) {
        if (isset($course['certifaction_test_original'])) {
            if ($course['certifaction_test_original'] == 'Sin Intentos Gratis') {
                $mbi_and_msp_siF++;
            }
        }
        $ExcelEmpresarial = $course['name'] == 'Excel Empresarial';
        if ($ExcelEmpresarial) {
            $excel =  $course;
        }
    }

    if(sizeof($one_month) > 0){
        if(sizeof($one_month) == 1)
            $text .= 'Está por vencer tu curso: '. "\n";
        else
            $text .= 'Están por vencer tus cursos: '. "\n";  
        foreach ($one_month as $course) {
            $text .= $course['name']. "\n";

        } 
        if ($mbi_and_msp_siF == 1) {
            $text .= ' 🚨 Actualmente este curso se encuentra reprobado y no brindamos certificados por participación.'. "\n";
        } elseif ($mbi_and_msp_siF == 2) {
            $text .= '🚨 Actualmente estos cursos se encuentran reprobados y no brindamos certificados por participación.'. "\n";
        }
        
        if ($mbi_and_msp_siF == 2 && (
        $excel['nivel_basico']['certifaction_test_original'] == 'Sin Intentos Gratis' 
        || $excel['nivel_intermedio']['certifaction_test_original'] == 'Sin Intentos Gratis' 
        || $excel['nivel_avanzado']['certifaction_test_original'] == 'Sin Intentos Gratis' ) ) 
        {
            $text .= "🚨 Actualmente estos cursos *se encuentran reprobados y para Excel, debes aprobar los 3 niveles,* 
            porque no brindamos certificados por participación, ni por nivel independiente, y este es el estado de cada nivel del curso:". "\n";
            $text .= "- NIVEL BASICO ESTADO: ". "\n";
            $text .= $excel['nivel_basico']['certifaction_test_original']. "\n";
             $text .= "- NIVEL INTERMEDIO ESTADO:". "\n";
             $text .= $excel['nivel_intermedio']['certifaction_test_original']. "\n";
             $text .= "- NIVEL AVANZADO ESTADO: ". "\n";
             $text .= $excel['nivel_avanzado']['certifaction_test_original']. "\n";
            
        }

        if ($mbi_and_msp_siF == 0 && (
        $excel['nivel_basico']['certifaction_test_original'] == 'Sin Intentos Gratis' 
        || $excel['nivel_intermedio']['certifaction_test_original'] == 'Sin Intentos Gratis' 
        || $excel['nivel_avanzado']['certifaction_test_original'] == 'Sin Intentos Gratis' ) ) 
        {
            $text .= "🚨 A continuación te indico el estado actual de cada nivel:". "\n";
            $text .= "- NIVEL BASICO ESTADO: ". "\n";
            $text .= $excel['nivel_basico']['certifaction_test_original']. "\n";
             $text .= "- NIVEL INTERMEDIO ESTADO:". "\n";
             $text .= $excel['nivel_intermedio']['certifaction_test_original']. "\n";
             $text .= "- NIVEL AVANZADO ESTADO: ". "\n";
             $text .= $excel['nivel_avanzado']['certifaction_test_original']. "\n";
            
        }
    }
@endphp

{{$text}}
{{-- 

 linea 42 y 43 del excel 
🚩 🚩 *Pero no todo está perdido.*
*Puedes realizar el pago para ponderar los intentos de examen que reprobaste* --}}








  


<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReporteMonitoreoExport implements WithStyles, WithEvents
{
    protected $monitoreo;
    protected $caracteristicas;
    protected $operadorDueno;

    public function __construct($monitoreo, $caracteristicas, $operadorDueno)
    {
        $this->monitoreo = $monitoreo;
        $this->caracteristicas = $caracteristicas;
        $this->operadorDueno = $operadorDueno;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Anchos de columna fijos para evitar que el texto se corte
                $sheet->getColumnDimension('A')->setWidth(30);
                $sheet->getColumnDimension('B')->setWidth(20);
                $sheet->getColumnDimension('C')->setWidth(20);
                $sheet->getColumnDimension('D')->setWidth(25);

                // --- TÍTULO PRINCIPAL ---
                $sheet->mergeCells('A1:D1');
                $sheet->setCellValue('A1', 'BITÁCORA DE CONTROL HIDROPÓNICA');
                $sheet->getStyle('A1')->getFont()->setName('Arial')->setSize(14)->setBold(true)->getColor()->setRGB('FFFFFF');
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1E3A8A');

                // --- METADATOS ---
                $sheet->setCellValue('A3', 'Fecha de Captura:');
                $sheet->setCellValue('B3', Carbon::parse($this->monitoreo->fecha)->format('d/m/Y'));
                $sheet->setCellValue('C3', 'Operador:');
                $sheet->setCellValue('D3', $this->operadorDueno);
                $sheet->setCellValue('A4', 'Sector:');
                $sheet->setCellValue('B4', $this->monitoreo->sector);

                // --- SECCIÓN 1: CARACTERÍSTICAS ---
                $sheet->mergeCells('A6:D6');
                $sheet->setCellValue('A6', '1. CARACTERÍSTICAS INICIALES DEL ÁREA');
                $sheet->setCellValue('A7', 'Superficie:');
                $sheet->setCellValue('B7', ($this->caracteristicas ? $this->caracteristicas->superficie_m2 : 'Sin datos') . ' m²');
                $sheet->setCellValue('C7', 'Variedad Instalada:');
                $sheet->setCellValue('D7', $this->caracteristicas ? $this->caracteristicas->variedad : 'Sin datos');
                $sheet->setCellValue('A8', 'Fecha Trasplante:');
                $sheet->setCellValue('B8', $this->caracteristicas ? Carbon::parse($this->caracteristicas->fecha_trasplante)->format('d/m/Y') : 'Sin datos');

                // --- SECCIÓN 2: VARIABLES MÉTRICAS ---
                $sheet->mergeCells('A10:D10');
                $sheet->setCellValue('A10', '2. VARIABLES MÉTRICAS Y BALANCES DIARIOS');
                $sheet->setCellValue('A11', 'Temperatura Ambiente:'); $sheet->setCellValue('B11', $this->monitoreo->temperatura . ' °C');
                $sheet->setCellValue('C11', 'Humedad Relativa:');      $sheet->setCellValue('D11', $this->monitoreo->humedad . ' %');
                $sheet->setCellValue('A12', 'DPV Calculado:');         $sheet->setCellValue('B12', $this->monitoreo->dpv . ' kPa');
                $sheet->setCellValue('C12', 'Estatus General Clima:'); $sheet->setCellValue('D12', $this->monitoreo->estatus_general);
                $sheet->setCellValue('A13', 'Vol. Riego Entrada:');    $sheet->setCellValue('B13', number_format($this->monitoreo->vol_riego_entrada) . ' mL');
                $sheet->setCellValue('C13', 'Vol. Drenaje Salida:');   $sheet->setCellValue('D13', number_format($this->monitoreo->vol_drenaje_salida) . ' mL');
                $sheet->setCellValue('A14', 'Porcentaje Drenaje:');    $sheet->setCellValue('B14', $this->monitoreo->porcentaje_drenaje . ' %');
                $sheet->setCellValue('C14', 'Caída Nocturna Sustrato:');$sheet->setCellValue('D14', $this->monitoreo->porcentaje_caida_nocturna . ' %');

                // --- SECCIÓN 3: PARÁMETROS QUÍMICOS MATRIZ ---
                $sheet->mergeCells('A16:D16');
                $sheet->setCellValue('A16', '3. PARÁMETROS QUÍMICOS Y DIFERENCIALES');
                $sheet->fromArray([
                    ['Parámetro', 'Entrada', 'Salida', 'Diferencial (Δ)'],
                    ['Conductividad Eléctrica (CE)', $this->monitoreo->ce_entrada, $this->monitoreo->ce_salida, $this->monitoreo->diferencia_ce],
                    ['Potencial de Hidrógeno (pH)', $this->monitoreo->ph_entrada, $this->monitoreo->ph_salida, $this->monitoreo->diferencia_ph]
                ], null, 'A17');

                $sheet->getStyle('A17:D17')->getFont()->setBold(true);
                $sheet->getStyle('A17:D17')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F1F5F9');

                // --- SECCIÓN 4: RADIACIÓN ---
                $sheet->mergeCells('A21:D21');
                $sheet->setCellValue('A21', '4. RADIACIÓN SOLAR Y COMPORTAMIENTO');
                $sheet->setCellValue('A22', 'Hora Captura:');   $sheet->setCellValue('B22', Carbon::parse($this->monitoreo->radiacion_hora)->format('g:i a'));
                $sheet->setCellValue('C22', 'Lectura Tomada:');     $sheet->setCellValue('D22', number_format($this->monitoreo->radiacion_lectura) . ' Lux');
                $sheet->setCellValue('A23', 'Semáforo Radiación:'); $sheet->setCellValue('B23', $this->monitoreo->radiacion_semaforo);
                $sheet->setCellValue('C23', 'Acción Ejecutada:');   $sheet->setCellValue('D23', $this->monitoreo->radiacion_accion_tomada ?? 'Ninguna');

                // --- ESTILOS VISUALES ---
                $filasSubtitulos = ['A3', 'C3', 'A4', 'A7', 'C7', 'A8', 'A11', 'C11', 'A12', 'C12', 'A13', 'C13', 'A14', 'C14', 'A22', 'C22', 'A23', 'C23'];
                foreach ($filasSubtitulos as $celda) {
                    $sheet->getStyle($celda)->getFont()->setBold(true)->getColor()->setRGB('475569');
                }

                $secciones = ['A6', 'A10', 'A16', 'A21'];
                foreach ($secciones as $seccion) {
                    $sheet->getStyle($seccion)->getFont()->setBold(true)->getColor()->setRGB('1E293B');
                    $sheet->getStyle($seccion)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E2E8F0');
                }

                $styleBorder = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CBD5E1'],
                        ],
                    ],
                ];
                $sheet->getStyle('A3:D4')->applyFromArray($styleBorder);
                $sheet->getStyle('A6:D8')->applyFromArray($styleBorder);
                $sheet->getStyle('A10:D14')->applyFromArray($styleBorder);
                $sheet->getStyle('A16:D19')->applyFromArray($styleBorder);
                $sheet->getStyle('A21:D23')->applyFromArray($styleBorder);
            },
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['name' => 'Arial', 'size' => 11]],
        ];
    }
}
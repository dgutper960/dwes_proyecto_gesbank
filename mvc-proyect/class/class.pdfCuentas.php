<?php
// Previamente, hemos desacrgado la librería fpdf y descomprimido en /fpdf
// http://www.fpdf.org/
// Cargamos la librería
// require('fpdf/fpdf.php');

// Creamos la clase para la sección Cuentas y extendemos de FPDF
class pdfCuentas extends FPDF
{

    function __construct()
    {
        // Cargamos el constructor del padre
        parent::__construct();

        // Al instanciar añade una página
        $this->AddPage();

        // Invocamos el método título
        $this->titulo();
    }

    // Creamos una funcion para la encabezado
    public function Header()
    {
        // Establecemos el estilo de la fuente
        $this->SetFont('Arial', 'B', 10);

        // VAMOS ESTABLECIENDO LAS CELDAS

        // Juego de caracteres, titulo , alineamos a la izquierda
        $this->Cell(1, 10, iconv('UTF-8', 'ISO-8859-1', 'Gesbank 1.0'), 0, 0, 'L');

        // Nombre: Negrita, Centrado
        $this->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1', 'David Gutiérrez Pérez'), 'B', 0, 'C');

        // Justificado a la izquierda
        $this->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1', '2DAW 23/24'), 0, 1, 'R');

        // Ln = salto de linea
        $this->Ln(5);
    }

    // Creamos una funcion para el footer
    public function Footer()
    {
        // número del footer
        $this->AliasNbPages();

        // Posición vertical
        $this->SetY(-10);

        // Estilo de fuente
        $this->SetFont('Arial', 'B', 10);

        // Celda para Núm Página
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 'T', 0, 'C');
    }

    // Funcion para título
    public function titulo()
    {
        // Estilo fuente
        $this->SetFont('Arial', 'B', 12);

        // BackGround color
        $this->SetFillColor(169, 223, 233);

        // Título
        $this->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1', 'Informe: Listado de Cuentas'), 0, 1, 'C', true);

        // Celda que establece fecha y hora actual
        $this->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1', 'Fecha: ' . date('d/m/Y H:i')), 0, 1, 'C', true);

        // Ln = Salto de línea
        $this->Ln(5);
    }

    public function encabezado()
    {

        //Definimos el tipo de fuente y tamaño
        $this->SetFont('Arial', 'B', 12);

        //Ponemos color de fondo
        $this->SetFillColor(226, 189, 254);

        //Escribimos el texto
        $this->Cell(10, 7, iconv('UTF-8', 'ISO-8859-1', 'id'), 'B', 0, 'R', true);
        $this->Cell(40, 7, iconv('UTF-8', 'ISO-8859-1', 'Nº Cuenta'), 'B', 0, 'C', true);
        $this->Cell(50, 7, iconv('UTF-8', 'ISO-8859-1', 'Cliente'), 'B', 0, 'C', true);
        $this->Cell(30, 7, iconv('UTF-8', 'ISO-8859-1', 'Fecha Alta'), 'B', 0, 'C', true);
        $this->Cell(30, 7, iconv('UTF-8', 'ISO-8859-1', 'Nº Mov'), 'B', 0, 'C', true);
        $this->Cell(30, 7, iconv('UTF-8', 'ISO-8859-1', 'Saldo'), 'B', 1, 'R', true);

        //Salto de línea
        $this->Ln(5);
    }

    // FUnción contenido
    // Como parámetro de entrada -> fetch de Cuentas

    function Contenido($cuentas)
    {
        // Encabezado -> invocamos encabezado()
        $this->encabezado();

        //Definimos el tamaño y el tipo de fuente
        $this->SetFont('Arial', '', 10);

        $this->SetFillColor(220, 235, 255);

        // Recorremos el fetch de Cuentas y vamos insertando
        foreach ($cuentas as $cuenta) {
            // Creamos una celda para cada propiedad con los parámetros adecuados (ajustamos a ojo de cubero)
            $this->Cell(10, 7, mb_convert_encoding($cuenta->id, 'ISO-8859-1', 'UTF-8'), 0, 0, 'R');
            $this->Cell(40, 7, mb_convert_encoding($cuenta->num_cuenta, 'ISO-8859-1', 'UTF-8'), 0, 0, 'C');
            $this->Cell(50, 7, mb_convert_encoding($cuenta->cliente, 'ISO-8859-1', 'UTF-8'), 0, 0, 'C');
            $this->Cell(30, 7, mb_convert_encoding($cuenta->fecha_alta, 'ISO-8859-1', 'UTF-8'), 0, 0, 'C');
            $this->Cell(30, 7, mb_convert_encoding($cuenta->num_movtos, 'ISO-8859-1', 'UTF-8'), 0, 0, 'C');
            $this->Cell(30, 7, mb_convert_encoding($cuenta->saldo, 'ISO-8859-1', 'UTF-8'), 0, 1, 'R');

            // Para controlar el fin de página:
            // PageBreakTrigger -> Se dipara cuando el puntero llega al margen de la página
            // //GetY-> Obtiene la posición del puntero
            if ($this->GetY() + 7 > $this->PageBreakTrigger) { // Cuando el puntero llega al margen:
                $this->AddPage(); // Creamos una nueva pág
                $this->encabezado(); // Cargamos otra vez la encabezado
                $this->SetFont('Arial', '', 10); // Establecemos el estilo de la fuente
            }
        }
    }
}
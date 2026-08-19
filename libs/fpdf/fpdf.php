<?php
declare(strict_types=1);

/**
 * Minimal FPDF-compatible implementation for simple text certificates.
 * It supports the subset of methods used by this project: AddPage, SetFont,
 * SetTextColor, Cell, MultiCell, Ln and Output.
 */
class FPDF
{
    private array $pages = [];
    private int $page = -1;
    private float $x = 40.0;
    private float $y = 60.0;
    private float $fontSize = 12.0;
    private array $textColor = [0, 0, 0];
    private float $pageWidth = 842.0;
    private float $pageHeight = 595.0;

    public function AddPage(string $orientation = 'P'): void
    {
        $this->page++;
        if (strtoupper($orientation) === 'L') {
            $this->pageWidth = 842.0;
            $this->pageHeight = 595.0;
        } else {
            $this->pageWidth = 595.0;
            $this->pageHeight = 842.0;
        }
        $this->pages[$this->page] = [];
        $this->x = 40.0;
        $this->y = 60.0;
    }

    public function SetFont(string $family, string $style = '', float $size = 12.0): void
    {
        $this->fontSize = $size;
    }

    public function SetTextColor(int $r, int $g, int $b): void
    {
        $this->textColor = [$r, $g, $b];
    }

    public function SetXY(float $x, float $y): void
    {
        $this->x = $x;
        $this->y = $y;
    }

    public function Ln(float $height = 8.0): void
    {
        $this->y += $height;
        $this->x = 40.0;
    }

    public function Cell(float $w, float $h = 0.0, string $txt = '', int $border = 0, int $ln = 0, string $align = ''): void
    {
        $x = $this->x;
        if (strtoupper($align) === 'C') {
            $textWidth = max(strlen($txt), 1) * ($this->fontSize * 0.34);
            $available = $w > 0 ? $w : ($this->pageWidth - 80.0);
            $x = (($this->pageWidth - $available) / 2.0) + (($available - $textWidth) / 2.0);
        }
        $this->pages[$this->page][] = [
            'x' => $x,
            'y' => $this->y,
            'text' => $txt,
            'size' => $this->fontSize,
            'color' => $this->textColor,
        ];
        if ($ln > 0) {
            $this->y += $h > 0 ? $h : ($this->fontSize + 2.0);
            $this->x = 40.0;
        } else {
            $this->x += $w;
        }
    }

    public function MultiCell(float $w, float $h, string $txt, int $border = 0, string $align = 'L'): void
    {
        $words = preg_split('/\s+/', trim($txt)) ?: [];
        $line = '';
        $maxChars = max((int) floor(($w > 0 ? $w : 700.0) / max($this->fontSize * 0.42, 1)), 1);
        foreach ($words as $word) {
            $candidate = trim($line . ' ' . $word);
            if (mb_strlen($candidate) > $maxChars && $line !== '') {
                $this->Cell($w, $h, $line, $border, 1, $align);
                $line = $word;
            } else {
                $line = $candidate;
            }
        }
        if ($line !== '') {
            $this->Cell($w, $h, $line, $border, 1, $align);
        }
    }

    public function Output(string $dest = 'I', string $name = 'document.pdf'): string
    {
        $pdf = $this->buildPdf();
        if ($dest === 'F') {
            file_put_contents($name, $pdf);
            return $name;
        }

        if ($dest === 'S') {
            return $pdf;
        }

        header('Content-Type: application/pdf');
        header('Content-Length: ' . strlen($pdf));
        if ($dest === 'D') {
            header('Content-Disposition: attachment; filename="' . basename($name) . '"');
        }
        echo $pdf;
        return $pdf;
    }

    private function buildPdf(): string
    {
        $objects = [];
        $pageObjectIds = [];
        $contentObjectIds = [];

        $objects[] = '1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj';
        $objects[] = '2 0 obj << /Type /Pages /Kids [';

        $nextObject = 3;
        foreach ($this->pages as $index => $items) {
            $pageObjectIds[$index] = $nextObject++;
            $contentObjectIds[$index] = $nextObject++;
        }

        $kids = [];
        foreach ($pageObjectIds as $id) {
            $kids[] = $id . ' 0 R';
        }
        $objects[1] = '2 0 obj << /Type /Pages /Kids [' . implode(' ', $kids) . '] /Count ' . count($kids) . ' >> endobj';

        foreach ($this->pages as $index => $items) {
            $content = "BT\n/F1 12 Tf\n";
            foreach ($items as $item) {
                $r = $item['color'][0] / 255;
                $g = $item['color'][1] / 255;
                $b = $item['color'][2] / 255;
                $text = $this->escapeText($item['text']);
                $y = $this->pageHeight - $item['y'];
                $content .= sprintf("%.3F %.3F %.3F rg\n/F1 %.2F Tf\n1 0 0 1 %.2F %.2F Tm\n(%s) Tj\n", $r, $g, $b, $item['size'], $item['x'], $y, $text);
            }
            $content .= "ET";
            $pageId = $pageObjectIds[$index];
            $contentId = $contentObjectIds[$index];
            $objects[] = $pageId . ' 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 ' . $this->pageWidth . ' ' . $this->pageHeight . '] /Resources << /Font << /F1 ' . ($nextObject) . ' 0 R >> >> /Contents ' . $contentId . ' 0 R >> endobj';
            $objects[] = $contentId . ' 0 obj << /Length ' . strlen($content) . ' >> stream' . "\n" . $content . "\nendstream endobj";
        }

        $objects[] = $nextObject . ' 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj';

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object . "\n";
        }
        $xref = strlen($pdf);
        $pdf .= 'xref' . "\n";
        $pdf .= '0 ' . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf('%010d 00000 n ', $offsets[$i]) . "\n";
        }
        $pdf .= 'trailer << /Size ' . (count($objects) + 1) . ' /Root 1 0 R >>' . "\n";
        $pdf .= 'startxref' . "\n" . $xref . "\n%%EOF";
        return $pdf;
    }

    private function escapeText(string $text): string
    {
        $replacements = ['\\' => '\\\\', '(' => '\\(', ')' => '\\)', "\r" => '', "\n" => ' '];
        return strtr($text, $replacements);
    }
}

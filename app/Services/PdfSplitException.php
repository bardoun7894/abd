<?php

namespace App\Services;

use RuntimeException;

/** Thrown when a PDF cannot be split per-page (e.g. compressed xref). */
class PdfSplitException extends RuntimeException
{
}

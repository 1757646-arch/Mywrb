<?php

namespace App\Actions\Filemanager;

use Illuminate\Http\Request;
use League\Flysystem\Filesystem;

class PasteFilesAction
{

    public function __construct(private Filesystem $filesystem) {}

    public function execute(Request $r)
    {
        $r->validate([
            'filesToPaste' => 'required|array',
            'intoPath' => 'required|string',
            'pasteFromAction' => 'required|in:cut,copy'
        ]);

        try {
            $filesystem = $this->filesystem;

            foreach ($r->filesToPaste as $file) {
                if (!$filesystem->fileExists($file) && !$filesystem->directoryExists($file)) {
                    throw new \Exception($file . ' does not exist!');
                }

                if ($r->pasteFromAction === 'copy') {
                    if ($filesystem->directoryExists($file)) {
                        $filesystem->copyDirectory($file, $r->intoPath . '/' . basename($file));
                    } else {
                        $filesystem->copy($file, $r->intoPath . '/' . basename($file));
                    }
                } else {
                    $filesystem->move($file, $r->intoPath . '/' . basename($file));
                }
            }

            return response()->json([
                'message' => 'Files pasted successfully',
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'error' => $exception->getMessage(),
            ], 500);
        };
    }
}

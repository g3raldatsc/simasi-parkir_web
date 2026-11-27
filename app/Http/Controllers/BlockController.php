<?php

namespace App\Http\Controllers;

use App\Http\Requests\BlockRequest;
use App\Models\Block;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BlockController extends Controller
{
    public function index(): View
    {
        $blocks = Block::query()
            ->withCount([
                'slots',
                'slots as active_slots_count' => fn ($q) => $q->where('is_active', true),
            ])
            ->orderBy('code')
            ->get();

        return view('blocks.index', compact('blocks'));
    }

    public function edit(Block $block): View
    {
        return view('blocks.edit', compact('block'));
    }

    public function update(BlockRequest $request, Block $block): RedirectResponse
    {
        $block->update($request->validated());

        return redirect()->route('blocks.index')
            ->with('success', "Nama Blok {$block->code} berhasil diperbarui.");
    }
}

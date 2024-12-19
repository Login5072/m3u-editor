<?php

namespace App\Filament\Resources\ChannelResource\Pages;

use App\Filament\Exports\ChannelExporter;
use App\Filament\Imports\ChannelImporter;
use App\Filament\Resources\ChannelResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListChannels extends ListRecords
{
    protected static string $resource = ChannelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
            Actions\ImportAction::make()
                ->importer(ChannelImporter::class)
                ->label('Import Channels')
                ->icon('heroicon-m-arrow-down-tray')
                ->modalDescription('Import channels from a CSV or XLSX file.'),
            Actions\ExportAction::make()
                ->exporter(ChannelExporter::class)
                ->label('Export Channels')
                ->icon('heroicon-m-arrow-up-tray')
                ->modalDescription('Export all channels to a CSV or XLSX file.')
                ->columnMapping(false)
                ->modifyQueryUsing(fn($query, array $options) => $query->where('playlist_id', $options['playlist'])),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Channels'),
            'enabled' => Tab::make('Enabled Channels')
                ->modifyQueryUsing(function ($query) {
                    return $query->where('enabled', true);
                }),
            'disabled' => Tab::make('Disabled Channels')
                ->modifyQueryUsing(function ($query) {
                    return $query->where('enabled', false);
                }),
        ];
    }
}

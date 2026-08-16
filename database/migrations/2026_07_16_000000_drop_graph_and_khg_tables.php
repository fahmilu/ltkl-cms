<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop the Graph, Layer, MasterLayerType, Peatland and Portfolio tables.
     *
     * Children referencing peatlands via foreign keys are dropped first to
     * avoid constraint violations.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        // Graph tables (FK -> peatlands)
        Schema::dropIfExists('graph_file_upload');
        Schema::dropIfExists('graph_field_observation');
        Schema::dropIfExists('graph_carbon_stock');
        Schema::dropIfExists('graph_land_use');
        Schema::dropIfExists('graph_population');
        Schema::dropIfExists('graph_rainfall');
        Schema::dropIfExists('graph_climate');
        Schema::dropIfExists('graph_burnt_area');
        Schema::dropIfExists('graph_land_cover');
        Schema::dropIfExists('graph_tree_loss');
        Schema::dropIfExists('graph_peat_general');

        // Layers (FK -> peatlands)
        Schema::dropIfExists('layers');

        // Layer type master data
        Schema::dropIfExists('master_layer_types');

        // Portfolios (independent)
        Schema::dropIfExists('portfolios');

        // Peatlands (parent, dropped last)
        Schema::dropIfExists('peatlands');

        Schema::enableForeignKeyConstraints();
    }

    /**
     * This migration is irreversible; the feature schema has been removed.
     */
    public function down(): void
    {
        // No-op: the Graph/KHG feature tables were intentionally removed.
    }
};

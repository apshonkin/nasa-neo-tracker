<?php

declare(strict_types=1);

use yii\db\Migration;

// Фотография дня, уже полученная от NASA. Нужна, чтобы после установки
// на главной сразу что-то было - без крона и без ключа.
class m260910_150416_seed_apod_example extends Migration
{
    public function safeUp(): void
    {
        $this->insert('{{%apod}}', [
            'date' => '2026-09-10',
            'title' => 'LDN 1295: The Giraffe Nebula',
            'explanation' => <<<'TEXT'
What does this image look like to you?   Many see a giraffe facing right, with neck stretched high and long legs mid-stride (but some may see a squirrel instead).   The featured image shows LDN 1295, also called the Giraffe Nebula, in the constellation of the mythical queen of Aethiopia (Cassiopeia).   It is an object in the Lynds Catalogue of Dark Nebulas, compiled in 1962 by American astronomer Beverly Lynds, a pioneer for women in astronomy and astrophysics.   Dark nebulas are interstellar clouds of dust and gas that block the visible light of the stars behind them.   These nebulas are often faint and challenging targets for astrophotographers.   Why do we see animal shapes and faces in nebulas, clouds, and pretty much everywhere?   It is due to pareidolia, our tendency to look for familiar patterns.   Pareidolia may provide animals with an evolutionary advantage, for example in identifying (and avoiding) predators.     APOD's main NASA site is moving : From apod.nasa.gov to science.nasa.gov/apod
TEXT,
            'media_type' => 'image',
            'url' => 'https://apod.nasa.gov/apod/image/2609/LDN1295_giraffe_1024.jpg',
            'hdurl' => 'https://apod.nasa.gov/apod/image/2609/LDN1295_giraffe.jpg',
            'thumbnail_url' => null,
            'copyright' => 'Alessandro Merga Text: Cecilia Chirenti (NASA GSFC, UMCP, CRESST II)',
        ]);
    }

    public function safeDown(): void
    {
        $this->delete('{{%apod}}', ['date' => '2026-09-10']);
    }
}

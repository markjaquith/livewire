<?php

namespace Livewire\Features\SupportLazyLoading;

use Tests\BrowserTestCase;
use Livewire\Livewire;
use Livewire\Component;

class BrowserTest extends BrowserTestCase
{
    /** @test */
    public function can_lazy_load_a_component()
    {
        Livewire::visit([new class extends Component {
            public function render() { return <<<HTML
            <div>
                <livewire:child lazy />
            </div>
            HTML; }
        }, 'child' => new class extends Component {
            public function mount() {
                sleep(1);
            }

            public function render() {
                return <<<HTML
                <div id="child">
                    Child!
                </div>
                HTML;
            }
        }])
        ->assertDontSee('Child!')
        ->waitFor('#child')
        ->assertSee('Child!')
        ;
    }

    /** @test */
    public function can_lazy_load_a_component_with_a_placeholder()
    {
        Livewire::visit([new class extends Component {
            public function render() { return <<<HTML
            <div>
                <livewire:child lazy />
            </div>
            HTML; }
        }, 'child' => new class extends Component {
            public function mount() { sleep(1); }
            public function placeholder() { return <<<HTML
                <div id="loading">
                    Loading...
                </div>
                HTML; }
            public function render() { return <<<HTML
            <div id="child">
                Child!
            </div>
            HTML; }
        }])
        ->assertSee('Loading...')
        ->assertDontSee('Child!')
        ->waitFor('#child')
        ->assertDontSee('Loading...')
        ->assertSee('Child!')
        ;
    }

    /** @test */
    public function can_pass_props_to_lazyilly_loaded_component()
    {
        Livewire::visit([new class extends Component {
            public $count = 1;
            public function render() { return <<<'HTML'
            <div>
                <livewire:child :$count lazy />
            </div>
            HTML; }
        }, 'child' => new class extends Component {
            public $count;
            public function mount() { sleep(1); }
            public function render() { return <<<'HTML'
            <div id="child">
                Count: {{ $count }}
            </div>
            HTML; }
        }])
        ->waitFor('#child')
        ->assertSee('Count: 1')
        ;
    }

    /** @test */
    public function can_pass_props_to_mount_method_to_lazyilly_loaded_component()
    {
        Livewire::visit([new class extends Component {
            public $count = 1;
            public function render() { return <<<'HTML'
            <div>
                <livewire:child :$count lazy />
            </div>
            HTML; }
        }, 'child' => new class extends Component {
            public $count;
            public function mount($count) { $this->count = $this->count + 2; }
            public function render() { return <<<'HTML'
            <div id="child">
                Count: {{ $count }}
            </div>
            HTML; }
        }])
        ->waitFor('#child')
        ->assertSee('Count: 3')
        ;
    }

    /** @test */
    public function can_pass_reactive_props_to_lazyilly_loaded_component()
    {
        Livewire::visit([new class extends Component {
            public $count = 1;
            public function inc() { $this->count++; }
            public function render() { return <<<'HTML'
            <div>
                <livewire:child :$count lazy />
                <button wire:click="inc" dusk="button">+</button>
            </div>
            HTML; }
        }, 'child' => new class extends Component {
            #[Prop(reactive: true)]
            public $count;
            public function mount() { sleep(1); }
            public function render() { return <<<'HTML'
            <div id="child">
                Count: {{ $count }}
            </div>
            HTML; }
        }])
        ->waitFor('#child')
        ->waitForText('Count: 1')
        ->assertSee('Count: 1')
        ->waitForLivewire()->click('@button')
        ->waitForText('Count: 2')
        ->assertSee('Count: 2')
        ->waitForLivewire()->click('@button')
        ->waitForText('Count: 3')
        ->assertSee('Count: 3')
        ;
    }
}

import { Component } from '@angular/core';
import { RouterOutlet } from '@angular/router';
import 'bootstrap/dist/js/bootstrap.bundle.min.js'

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [RouterOutlet],
  templateUrl: './app.component.html',
  styleUrl: './app.component.scss'
})
export class AppComponent {
  title = 'e-banking';
}

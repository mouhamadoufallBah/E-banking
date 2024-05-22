import { Component, Input, OnInit,  } from '@angular/core';
import { SidebarComponent } from '../sidebar/sidebar.component';
import { RouterOutlet } from '@angular/router';
import { ApiService } from '../../core/services/api-service.service';

@Component({
  selector: 'app-main',
  standalone: true,
  imports: [SidebarComponent, RouterOutlet],
  templateUrl: './main.component.html',
  styleUrl: './main.component.scss'
})
export class MainComponent implements OnInit {
  ngOnInit(): void {

  }


}

import { Component, OnInit } from '@angular/core';
import { ApiService } from '../../core/services/api-service.service';

@Component({
  selector: 'app-sidebar',
  standalone: true,
  imports: [],
  templateUrl: './sidebar.component.html',
  styleUrl: './sidebar.component.scss'
})
export class SidebarComponent implements OnInit {
  utilisateur: any;

  constructor(private apiService: ApiService) { }


  ngOnInit(): void {
    this.userConnected();
  }

  userConnected() {
    const user = JSON.parse(localStorage.getItem('userConnected') || '');
    return this.apiService.getUserConnected(user.id).then(
      (res) => {
        console.log(res.data);

        this.utilisateur = res.data
      }
    ).catch(
      (err) => {
        console.log(err);

      }
    );
  }

}

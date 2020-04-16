const express = require ('express');
const app = express();
const cors = require ('cors');

app.use(cors({
  origin: ['http://localhost:4200'],
  credentials: true,
}))
app.use(express.json());
app.use(express.urlencoded({extended: false}));

let figures = [
  {
    id: 1,
    type: 'Circle',
    area: 10,
  },
  {
    id: 2,
    type: 'Square',
    area: 25,
  },
  {
    id: 3,
    type: 'Rectangle',
    area: 21,
  },
  {
    id: 4,
    type: 'Triangle',
    area: 19,
  }, 
  {
    id: 5,
    type: 'Square',
    area: 25,
  },
];

app.get('/figures', (request, response) => {
  setTimeout( () =>{
    response.send(figures);
  }, 500);
});

app.delete('/figures', (request, response) => {
  const {id} = request.query;
  figures = figures.filter(figures => figures.id !== Number(id));
  setTimeout( () =>{
    response.send({success: true});
  }, 500);

})

app.post('/figures', (request, response) => {
  const {type, area} = request.body;
  let newId = 1;

  if (figures[figures.length-1] !== undefined){
    newId = figures[figures.length-1].id + 1;
  } 

  figures.push({
    id: newId,
    type,
    area
  });

  setTimeout( () =>{
    response.send({success: true, id: newId});
  }, 500);

});

app.listen(3000, () => { 
  console.log('Application works on the port 3000'); 
  });

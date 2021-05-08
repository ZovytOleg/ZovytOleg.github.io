package com.mycompany.laboratornaya16;

import java.applet.Applet;
import java.awt.Color;
import java.awt.Graphics;
import java.awt.Rectangle;
import java.awt.event.MouseEvent;
import java.awt.event.MouseListener;
import javax.swing.JApplet;


public class MyRect extends Applet implements MouseListener {
    Rectangle a = new Rectangle(20, 20, 100, 60);
    public void init() {
        setBackground(Color.yellow);
// реєстрація даного класу в якості блоку прослуховування
        addMouseListener(this);
    }

// реалізація всіх п’яти методів інтерфейсу MouseListener
    public void mouseClicked(MouseEvent me) {
        int x = me.getX();
        int y = me.getY();
        if (a.inside(x, y)) {
            System.out.println("клік в зеленому прямокутнику");
        } else {
            System.out.println("клік в жовтому фоні");
        }
    }

// реалізація таких методів порожня
    public void mouseEntered(MouseEvent e) {
    }

    public void mouseExited(MouseEvent e) {
    }

    public void mousePressed(MouseEvent e) {
    }

    public void mouseReleased(MouseEvent e) {
    }

    public void paint(Graphics g) {
        g.setColor(Color.green);
        g.fillRect(a.x, a.y, a.width, a.height);
    }
}
